<?php

namespace UniversiBO\ForumBundle\Integration;

use UniversiBO\Legacy\App\User;

/**
 * @author Davide Bellettini
 */
interface ForumApi
{
    public function getSidForUri();
    public function getOnlySid();
    public function getPath();
    public function login(User $user);
    public function logout();
    public function insertUser(User $user, $password);
    public function updateUserStyle(User $user);
    public function updatePassword(User $user, $password);
    public function updateUserEmail(User $user);
    public function addUserGroup(User $user, $group);
    public function removeUserGroup(User $user, $group);
    public function addGroup($title, $desc, $id_owner);
    public function addGroupForumPrivilegies($forum_id, $group_id);
    public function getMaxForumId();
    public function addForumInsegnamentoNewYear($forum_id, $anno_accademico);
    public function getPostUri($id_post);
    public function getLastPostsForum(User $user, $id_forum, $num = 10);
}