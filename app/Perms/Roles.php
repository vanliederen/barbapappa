<?php

namespace App\Perms;

/**
 * The roles trait, for easy roles management.
 * This should be used in classes defining role constants for some scope.
 */
trait Roles {

    /**
     * Get a key/value list of user role IDs and their display names.
     *
     * Important: When using this trait, classes must implementg
     * `protected $roles = [];` with a key/value map of role IDs and display names.
     *
     * @return {array} Key/value list of user roles.
     */
    public static function roles() {
        throw new \Exception("the static roles() function is not implemented properly in the roles class it is used in");
    }

    /**
     * Get the display name for a role with the given ID.
     *
     * An exception is thrown if the role ID is unknown.
     *
     * @param int $id The role ID.
     * @return string The display name for the role.
     * @throws \Exception Throws if the given role ID is unknown.
     */
    public static function roleName($id) {
        // Get the roles map
        $roles = Self::roles();

        // Ensure the ID is valid and exists
        if(empty($roles) || $id === null || !isset($roles[$id]))
            throw new \Exception("failed to get role name, unknown role ID given");

        return $roles[$id];
    }
}
