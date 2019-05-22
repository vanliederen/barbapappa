<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Helpers\ValidationDefaults;
use App\Perms\CommunityRoles;
use App\Perms\Builder\Config as PermsConfig;

class CommunityMemberController extends Controller {

    /**
     * Community member index page.
     *
     * @return Response
     */
    public function index() {
        return view('community.member.index');
    }

    /**
     * Show a member of a community with the given user ID.
     *
     * @return Response
     */
    public function show($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->users(['role', 'visited_at'])->where('user_id', $memberId)->firstOrfail();

        return view('community.member.show')
            ->with('member', $member);
    }

    /**
     * The edit page for a community member.
     *
     * @return Response
     */
    public function edit($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->users(['role'])->where('user_id', $memberId)->firstOrfail();

        // Show the edit view
        return view('community.member.edit')
            ->with('member', $member);
    }

    /**
     * Edit a community member.
     *
     * @return Response
     */
    public function doEdit(Request $request, $communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->users(['role'], true)->where('user_id', $memberId)->firstOrfail();
        $curRole = $member->pivot->role;
        $newRole = $request->input('role');

        // Build validation rules, validate
        $rules = [
            'role' => 'required|' . ValidationDefaults::communityRoles(),
        ];
        if($newRole != $curRole)
            $rules['confirm_role_change'] = 'accepted';
        $this->validate($request, $rules);

        // If manager or higher changed to lower role, and he was the last with
        // that role or higher, do not allow the change
        if($newRole < $curRole && $curRole > CommunityRoles::USER) {
            $hasOtherRanked = $community
                ->users(['role'], true)
                ->where('user_id', '<>', $memberId)
                ->where('community_user.role', '>=', $curRole)
                ->limit(1)
                ->exists();
            if(!$hasOtherRanked)
                return redirect()
                    ->route('community.member.show', ['communityId' => $communityId, 'memberId' => $memberId])
                    ->with('error', __('pages.communityMembers.cannotDemoteLastManager'));
        }

        // Set the role ID, save the member
        $member->pivot->role = $newRole;
        $member->pivot->save();

        // Redirect to the show view after editing
        return redirect()
            ->route('community.member.index', ['communityId' => $communityId])
            ->with('success', __('pages.communityMembers.memberUpdated'));
    }

    /**
     * The page to delete a community member.
     *
     * @return Response
     */
    public function delete($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->users()->where('user_id', $memberId)->firstOrfail();

        return view('community.member.delete')
            ->with('member', $member);
    }

    /**
     * Make a member leave the community.
     *
     * @return Response
     */
    public function doDelete($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->users()->where('user_id', $memberId)->firstOrfail();

        // TODO: do not allow deletion if admin
        // TODO: do not allow deletion of self

        // Delete the member
        $community->leave($member);

        // Redirect to the index page after deleting
        return redirect()
            ->route('community.member.index', ['communityId' => $communityId])
            ->with('success', __('pages.communityMembers.memberRemoved'));
    }

    /**
     * The permission required for viewing.
     * @return PermsConfig The permission configuration.
     */
    public static function permsView() {
        return CommunityController::permsManage();
    }

    /**
     * The permission required for managing such as editing and deleting.
     * @return PermsConfig The permission configuration.
     */
    public static function permsManage() {
        // TODO: community managers should be able to change some settings?
        return CommunityController::permsAdminister();
    }
}
