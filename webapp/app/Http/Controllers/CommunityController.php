<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use App\Helpers\ValidationDefaults;
use App\Models\Community;
use App\Perms\AppRoles;
use App\Perms\CommunityRoles;

class CommunityController extends Controller {

    /**
     * Community overview page.
     *
     * @return Response
     */
    public function overview() {
        return view('community.overview')
            ->with('communities', Community::visible()->get());
    }

    /**
     * Community create page.
     *
     * @return Response
     */
    public function create() {
        return view('community.create');
    }

    /**
     * Create a community.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function doCreate(Request $request) {
        // Validate
        $this->validate($request, [
            'name' => 'required|' . ValidationDefaults::NAME,
            'slug' => 'nullable|' . ValidationDefaults::communitySlug(),
            'password' => 'nullable|' . ValidationDefaults::SIMPLE_PASSWORD,
        ], [
            'slug.regex' => __('pages.community.slugFieldRegexError'),
        ]);

        // Create the community
        $community = new Community();
        $community->name = $request->input('name');
        $community->slug = $request->has('slug') ? $request->input('slug') : null;
        $community->password = $request->has('password') ? $request->input('password') : null;
        $community->visible = is_checked($request->input('visible'));
        $community->public = is_checked($request->input('public'));
        $community->save();

        // Redirect the user to the community page
        return redirect()
            ->route('community.show', ['communityId' => $community->human_id])
            ->with('success', __('pages.community.created'));
    }


    /**
     * Community show page.
     *
     * @return Response
     */
    public function show($communityId) {
        // Get the community and session user
        $community = \Request::get('community');
        $user = barauth()->getSessionUser();

        // Update the visit time for this member
        $member = $community->users(['visited_at'], true)
            ->where('user_id', $user->id)
            ->first();
        if($member != null) {
            $member->pivot->visited_at = new \DateTime();
            $member->pivot->save();
        }

        return view('community.show')
            ->with('joined', $community->isJoined($user))
            ->with('bars', $community->bars()->visible()->get());
    }

    /**
     * Community edit page.
     *
     * @return Response
     */
    public function edit() {
        return view('community.edit');
    }

    /**
     * Community update endpoint.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function update(Request $request) {
        // Get the community
        $community = \Request::get('community');

        // Validate
        $this->validate($request, [
            'name' => 'required|' . ValidationDefaults::NAME,
            'slug' => 'nullable|' . ValidationDefaults::communitySlug($community),
            'password' => 'nullable|' . ValidationDefaults::SIMPLE_PASSWORD,
        ], [
            'slug.regex' => __('pages.community.slugFieldRegexError'),
        ]);

        // Change the name properties
        $community->name = $request->input('name');
        $community->slug = $request->has('slug') ? $request->input('slug') : null;
        $community->password = $request->has('password') ? $request->input('password') : null;
        $community->visible = is_checked($request->input('visible'));
        $community->public = is_checked($request->input('public'));

        // Save the community
        $community->save();

        // Redirect the user to the community page
        return redirect()
            ->route('community.show', ['communityId' => $community->human_id])
            ->with('success', __('pages.community.updated'));
    }

    /**
     * The community join confirmation page.
     *
     * @return Response
     */
    public function join($communityId) {
        // Get the community and user
        $community = \Request::get('community');
        $user = barauth()->getSessionUser();

        // Redirect to the community page if the user has already joined
        if($community->isJoined($user))
            return redirect()
                ->route('community.show', ['communityId' => $communityId]);

        // Show the community join confirm page
        return view('community.join');
    }

    /**
     * Make a user join the community.
     *
     * @return Response
     */
    public function doJoin(Request $request, $communityId) {
        // Get the community and user
        $community = \Request::get('community');
        $user = barauth()->getSessionUser();

        // Handle the password if required
        if($community->needsPassword($user)) {
            // Validate password field input
            $this->validate($request, [
                'code' => 'required|' . ValidationDefaults::CODE,
            ]);

            // Test the password
            if(!$community->isPassword($request->input('code'))) {
                // Mark the error and retur
                $validator = Validator::make([], []);
                $validator->errors()->add('code', __('pages.community.incorrectCode'));
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        // Join the user
        $community->join($user);

        // Redirect to the community page
        return redirect()
            ->route('community.show', ['communityId' => $communityId])
            ->with('success', __('pages.community.joinedThisCommunity'));
    }

    /**
     * The community leave confirmation page.
     *
     * @return Response
     */
    public function leave($communityId) {
        // TODO: make sure the user can leave this community

        // Get the community and user
        $community = \Request::get('community');
        $user = barauth()->getSessionUser();

        // Redirect to the community page if the user isn't joined
        if(!$community->isJoined($user))
            return redirect()
                ->route('community.show', ['communityId' => $communityId]);

        // Show the communtiy leave confirm page
        return view('community.leave');
    }

    /**
     * Make a user leave the community.
     *
     * @return Response
     */
    public function doLeave($communityId) {
        // TODO: make sure the user can leave the community

        // Get the community and user
        $community = \Request::get('community');
        $user = barauth()->getSessionUser();

        // Leave the user
        $community->leave($user);

        // Redirect to the community page
        return redirect()
            ->route('community.show', ['communityId' => $communityId])
            ->with('success', __('pages.community.leftThisCommunity'));
    }

    /**
     * The permission required for managing such as editing and deleting.
     * @return PermsConfig The permission configuration.
     */
    public static function permsManage() {
        return CommunityRoles::presetAdmin();
    }

    /**
     * The permission required for creating a new community.
     * @return PermsConfig The permission configuration.
     */
    public static function permsCreate() {
        return AppRoles::presetAdmin();
    }
}