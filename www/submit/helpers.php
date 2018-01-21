<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

defined('mnminclude') or die();

function getStep()
{
    $step = empty($_REQUEST['step']) ? '1' : $_REQUEST['step'];

    return in_array($step, array('1', '2', '3')) ? $step : '1';
}

function getLinkByRequestId($link, $from)
{
    $link->id = (int)(isset($from['id']) ? $from['id'] : 0);
    $link->read();

    return $link;
}

function returnToStep($step, $id = null)
{
    global $globals;

    $location = $globals['base_url'].'submit?step='.($id ? $step : 1);

    if ($id) {
        $location .= '&id='.$id;
    }

    die(header('Location: '.$location));
}

function addFormError($title, $info = '', $syslog = '')
{
    return addFormMessage('error', $title, $info, $syslog);
}

function addFormWarning($title, $info = '', $syslog = '')
{
    return addFormMessage('warning', $title, $info, $syslog);
}

function addFormMessage($type, $title, $info = '', $syslog = '')
{
    global $current_user, $$type;

    if (is_array($title)) {
        $data = $title;
    } else {
        $data = array(
            'title' => $title,
            'info' => $info,
            'syslog' => $syslog,
        );
    }

    if ($data['syslog']) {
        syslog(LOG_NOTICE, 'MENEAME ['.$current_user->user_login.'] '.$data['syslog']);
    }

    $$type = array(
        'title' => $data['title'],
        'info' => $data['info'],
    );
}

function validateLinkUrl($link, $validator)
{
    global $site, $site_properties, $current_user;

    if (empty($link->url) && !empty($site_properties['no_link'])) {
        return true;
    }

    if (empty($link->url)) {
        return addFormError(_('No se ha enviado ninguna URL'));
    }

    $anti_spam = empty($site_properties['no_anti_spam']);

    // Magnet and eLink check
    if(strtolower(substr($link->url,0,8))=="magnet:?" OR strtolower(substr($link->url,0,7))=="ed2k://") {
        try {
            $validator->checkP2PLink();
        } catch (Exception $e) {
            return;
        }
        return true;
    }

    try {
        $validator->fixUrl();
        $validator->checkUrl();
        $validator->checkLocal();
        $validator->checkDuplicates();
        $validator->checkRemote($anti_spam);
    } catch (Exception $e) {
        return;
    }

    if (!$link->pingback()) {
        $link->trackback();
    }

    $link->trackback = htmlspecialchars($link->trackback);

    $link->create_blog_entry();

    $blog = new Blog;
    $blog->id = $link->blog;
    $blog->read();

    if (empty($anti_spam) || $current_user->admin) {
        return true;
    }

    try {
        $validator->checkBan();
        $validator->checkBan($blog->url);
        $validator->checkRatio($blog);
        $validator->checkMedia();
        $validator->checkBlogSame($blog, 24);
        $validator->checkBlogFast($blog, 30);
        $validator->checkBlogHistory($blog, 60);
    } catch (Exception $e) {
        return;
    }

    if ($site->owner) {
        return true;
    }

    try {
        $validator->checkBlogOverflow($blog, 12);
        $validator->checkMediaOverflow(12);
        $validator->checkBanPunished();
    } catch (Exception $e) {
        return;
    }

    return true;
}
