<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$myId    = $me->getId();
$withId  = $with->getId();
$grouped = array();

foreach ($messages as $num => $dm) {
    if (empty($grouped)) {
        //first DM of the group
        if (isset($messages[$num + 1])) {
            $nextDate = $view['date']->toShort($messages[$num + 1]['dateSent']);
        } else {
            $nextDate = $view['date']->toShort($dm['dateSent']);
        }

        if ($dm['fromUser']['id'] === $myId) {
            $direction = '';
            $groupId   = $myId;
        } else {
            $direction = ' media-right';
            $groupId   = $withId;
        }
    }
    $msgDate = $view['date']->toShort($dm['dateSent']);

    //add the dm
    $grouped[] = $dm;

    if (!isset($messages[$num + 1]) || $messages[$num + 1]['fromUser']['id'] !== $groupId || $msgDate != $nextDate) {
        //last message or new group

        //now render the messages
        echo $view->render('MauticChatBundle:DM:group.html.php', array(
            'direction'           => $direction,
            'messages'            => $grouped,
            'user'                => $dm['fromUser'],
            'showDate'            => ($msgDate != $nextDate),
            'insertUnreadDivider' => (!empty($insertUnreadDivider) && $groupId !== $myId) ? true : false
        ));

        //reset the group
        $grouped = array();
    } else {
        //next message from same user so add it and move to the next
        continue;
    }
}