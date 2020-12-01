<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Helpers\ValidationDefaults;
use App\Managers\EmailVerificationManager;
use App\Models\Email;
use App\Models\User;
use App\Perms\AppRoles;

class EmailController extends Controller {

    /**
     * Emails page.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     *
     * @return Response
     */
    public function show(Request $request, $userId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        return view('account.email.overview');
    }

    /**
     * Email add page.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     *
     * @return Response
     */
    public function create(Request $request, $userId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        return view('account.email.create');
    }

    /**
     * Email add page.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     *
     * @return Response
     */
    public function doCreate(Request $request, $userId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        // Validate
        $this->validate($request, [
            'email' => 'required|' . ValidationDefaults::EMAIL,
        ]);

        // Do not allow if already verified or recently added by someone else
        $used = Email::where('email', $request->input('email'))
            ->where(function($query) use($userId) {
                $query
                    ->whereNotNull('verified_at')
                    ->orWhere('user_id', $userId)
                    ->orWhere('created_at', '>', now()->subMinutes(15));
            })
            ->limit(1)
            ->count() > 0;
        if($used) {
            add_session_error('email', __('auth.emailUsed'));
            return redirect()->back();
        }

        // Get the user
        $user = \Request::get('user');

        // Delete any existing email entries
        Email::where('email', $request->input('email'))
            ->delete();

        // Create the email address
        $email = new Email();
        $email->user_id = $user->id;
        $email->email = $request->input('email');
        $email->save();

        // Make an email verification request
        EmailVerificationManager::createAndSend($email, false);

        // Redirect to the emails page, show a success message
        return redirect()
            ->route('account.emails', ['userId' => $userId])
            ->with('success', __('pages.accountPage.addEmail.added'));
    }

    /**
     * Show a list of email addresses to the user that still need verification.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     */
    public function unverified($userId = null) {
        // Redirect to user page if user is undefined
        if($userId == null)
            return redirect()
                ->route('account.user.emails.unverified', ['userId' => barauth()->getUser()->id]);

        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));
        $user = User::findOrFail($userId);

        // List the unverified addresses
        $emails = $user->emails()->unverified()->get();
        if($emails->isEmpty())
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('success', __('pages.accountPage.email.allVerified'));

        $anyVerified = $user->emails()->verified()->limit(1)->count() > 0;

        return view('account.email.unverified')
            ->with('anyVerified', $anyVerified)
            ->with('emails', $emails);
    }

    /**
     * Send a verification email to all email addresses.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     */
    public function doVerifyAll(Request $request, $userId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));
        $user = User::findOrFail($userId);

        // List the unverified addresses
        $emails = $user->emails()->unverified()->get();
        if($emails->isEmpty())
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('success', __('pages.accountPage.email.allVerified'));

        // Send the verification emails
        foreach($emails as $email)
            EmailVerificationManager::createAndSend($email, false);

        return redirect()->route('account.emails.verified', ['userId' => $userId]);
    }

    /**
     * Landing page after sending a verification email to all unverified
     * addresses. This page should be shown until all are actually verified.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     */
    public function verified(Request $request, $userId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));
        $user = User::findOrFail($userId);

        // List unverified emails, redirect to last bar if there are none
        $emails = $user->emails()->unverified()->get();
        if($emails->isEmpty())
            return redirect()->route('last');

        // Keep showing current page until all email addresses are verified
        return view('account.email.verified')
            ->with('emails', $emails);
    }

    /**
     * Resend a verification email.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     * @param string $emailId The email ID.
     *
     * @return Response
     */
    public function reverify(Request $request, $userId, $emailId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        // Get the selected email address and user
        $email = Email::findOrFail($emailId);
        $user = \Request::get('user');

        // Ensure it isn't verified
        if($email->isVerified()) {
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('error', __('pages.accountPage.email.alreadyVerified'));
        }

        // Make an email verification request
        EmailVerificationManager::createAndSend($email, false);

        // Redirect to the emails page, show a success message
        return redirect()
            ->route('account.emails', ['userId' => $userId])
            ->with('success', __('pages.accountPage.email.verifySent'));
    }

    /**
     * The email address delete confirmation page.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     * @param string $emailId The email ID.
     *
     * @return Response
     */
    public function delete($userId, $emailId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        // Get the selected email address and user
        $email = Email::findOrFail($emailId);
        $user = \Request::get('user');

        // Count the number of verified e-mail addresses left after this
        // deletion
        $after = $user->emails()->count() - 1;
        $verifiedBefore = $user->emails()->verified()->count();
        $verifiedAfter = $user
            ->emails()
            ->verified()
            ->where('id', '!=', $email->id)
            ->count();

        // Ensure there are enough verified email addresses left
        if($after <= 0)
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('error', __('pages.accountPage.email.cannotDeleteMustHaveOne'));
        if($verifiedAfter <= 0 && $verifiedBefore !== $verifiedAfter)
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('error', __('pages.accountPage.email.cannotDeleteMustHaveVerified'));

        // Show the delete confirm page
        return view('account.email.delete')
            ->with('email', $email);
    }

    /**
     * Do delete an email address.
     *
     * @param Request $request The request.
     * @param string $userId The user ID.
     * @param string $emailId The email ID.
     *
     * @return Response
     */
    public function doDelete(Request $request, $userId, $emailId) {
        // To edit a different user, ensure we have administrator privileges
        if(barauth()->getSessionUser()->id != $userId && !perms(AppRoles::presetAdmin()))
            return response(view('noPermission'));

        // Get the selected email address and user
        $email = Email::findOrFail($emailId);
        $user = \Request::get('user');

        // Count the number of verified e-mail addresses left after this
        // deletion
        $after = $user->emails()->count() - 1;
        $verifiedBefore = $user->emails()->verified()->count();
        $verifiedAfter = $user
            ->emails()
            ->verified()
            ->where('id', '!=', $email->id)
            ->count();

        // Ensure there are enough verified email addresses left
        if($after <= 0)
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('error', __('pages.accountPage.email.cannotDeleteMustHaveOne'));
        if($verifiedAfter <= 0 && $verifiedBefore !== $verifiedAfter)
            return redirect()
                ->route('account.emails', ['userId' => $userId])
                ->with('error', __('pages.accountPage.email.cannotDeleteMustHaveVerified'));

        // Delete the email address
        $email->delete();

        // Redirect to the emails page, show a success message
        return redirect()
            ->route('account.emails', ['userId' => $userId])
            ->with('success', __('pages.accountPage.email.deleted'));
    }

    /**
     * Email preferences page.
     *
     * @return Response
     */
    public function preferences() {
        return (new PagesController)->index()
            ->with('info', __('pages.emailPreferencesNotYetImplemented'));
    }
}
