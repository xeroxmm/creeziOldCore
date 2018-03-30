<?php

class dbQueries
{
    public static function getUserIDByNameAndPassword(string $username, string $password): int
    {
        $sql = "SELECT ID FROM xcUsers WHERE username = " . db::harmAndString($username) . " AND userpass = " . db::harmAndString($password) . ";";
        $r = db::queryRaw($sql);

        return $r->firstResultInt('ID');
    }

    private static function transformQueryIntoUser(string $sql):?userObj
    {
        $r = db::queryRaw($sql);
        if ($r->amount < 1)
            return NULL;

        $user = new userObj();
        $user->setDataByDBObj($r->results[0]);

        return $user;
    }

    public static function getUserObjectByNameAndPassword(string $username, string $password):?userObj
    {
        $sql = "SELECT ID, username, usermail, isRemember, fullname, isSteam, isMail, isGoogle, isFacebook, isVK, isTwitter, isAuthed, dateLogin FROM xcUsers WHERE isMail = 1 AND username = " . db::harmAndString($username) . " AND userpass = " . db::harmAndString($password) . ";";
        return self::transformQueryIntoUser($sql);
    }

    public static function getUserObjectBySteamID(string $steamID):?userObj
    {
        $json = @json_decode(file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . APIKeySteam . '&steamids=' . $steamID));

        if (!isset($json->response) || !isset($json->response->players) || !isset($json->response->players[0]) || !isset($json->response->players[0]->steamid) || $json->response->players[0]->steamid != $steamID)
            return NULL;
        //echo $steamID.' -> '.str_replace("\n","<br />",print_r($json,true));
        $json = $json->response->players[0];
        $username = $json->personaname ?? 'na';
        $userAvatar = $json->avatar ?? 'na.jpg';
        $country = strtolower(substr($json->loccountrycode ?? '00', 0, 2));
        $profile = $json->profile ?? 'na';
        $md5 = md5($steamID . '-0010');

        $sql = "INSERT INTO xcUsers (username, userpass, usermail, dateCreatedHR, dateCreated, isRemember, fullname, isSteam, dateLogin, dateLoginHR, isAuthed, avatarURL, country) 
                VALUES (" . db::harmAndString($steamID) . "," . db::harmAndString($md5) . "," . db::harmAndString($profile) . ",NOW()," . time() . ",1," . db::harmAndString($username) . ",1," . time() . ",NOW(),1," . db::harmAndString($userAvatar) . "," . db::harmAndString($country) . ")
                ON DUPLICATE KEY UPDATE dateLogin = " . time() . ", dateLoginHR = NOW(), avatarURL = " . db::harmAndString($userAvatar) . ", fullName = " . db::harmAndString($username) . ";";

        db::queryRaw($sql);

        $sql = 'SELECT ID, username, isRemember, fullname, isSteam, isMail, isGoogle, isFacebook, isVK, isTwitter, isAuthed, dateLogin FROM xcUsers WHERE username = ' . db::harmAndString($steamID) . ' AND isSteam = 1;';

        return self::transformQueryIntoUser($sql);
    }
}