<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ChatBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\UserBundle\Entity\User;

/**
 * Class ChatModel
 * {@inheritdoc}
 * @package Mautic\CoreBundle\Model\FormModel
 */
class ChatModel extends FormModel
{

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticChatBundle:Chat');
    }

    /**
     * Get direct messages between current user and passed in user
     *
     * @param User      $withUser
     * @param null      $lastId
     * @param \DateTime $fromDate
     *
     * @return mixed
     */
    public function getDirectMessages(User $withUser, $lastId = null, \DateTime $fromDate = null)
    {
        if ($fromDate == null) {
            $fromDate  = $this->getChatHistoryDate($withUser);
        }
        return $this->getRepository()->getUserConversation($this->factory->getUser(), $withUser, $lastId, $fromDate);
    }

    /**
     * Get a list of users for chat
     *
     * @param string $search
     * @param int    $limit
     * @param int    $start
     *
     * @return mixed
     */
    public function getUserList($search = '', $limit = 10, $start = 0)
    {
        $repo  = $this->getRepository();
        $users = $repo->getUsers($this->factory->getUser()->getId(), $search, $limit, $start);

        //get users unread messages
        $unread = $repo->getUnreadMessageCount($this->factory->getUser()->getId(), array_keys($users));
        foreach ($unread as $u) {
            $users[$u['userId']]['unread'] = $u['unread'];
        }

        return $users;
    }

    /**
     * @param User $chattingWith
     *
     * @return \Mautic\CoreBundle\Helper\DateTimeHelper
     */
    public function getChatHistoryDate(User $chattingWith)
    {
        //save the from date from history so that the user doesn't have to wait to scroll back again
        $session = $this->factory->getSession();

        $fromDate = $session->get('mautic.chat.history.' . $chattingWith);
        if (empty($fromDate)) {
            //get today's chats only
            $fromDate = $this->factory->getDate(date('Y-m-d') . ' 00:00:00');
        } else {
            $fromDate = $this->factory->getDate(date('Y-m-d', strtotime($fromDate)) . ' 00:00:00');
        }
        $session->set('mautic.chat.history.' . $chattingWith, $fromDate->toLocalString());

        return $fromDate->getUtcDateTime();
    }

    public function markMessagesRead($chattingWithId, $lastId = 0)
    {
        $this->getRepository()->markRead($this->factory->getUser()->getId(), $chattingWithId, $lastId);
    }
}