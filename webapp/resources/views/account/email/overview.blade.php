@extends('layouts.app')

@section('title', __('pages.accountPage.email.yourEmails'))

@section('content')
    <h2 class="ui header">@yield('title')</h2>
    <p>@lang('pages.accountPage.email.description')</p>

    @php
        // Count configured and unverified email addresses
        $mailsConfigured = $user->emails()->count();
        $mailsUnverified = $user->emails()->unverified()->count();
    @endphp

    <table class="ui compact celled table">
        <thead>
            <tr>
                <th>@lang('account.email')</th>
                <th>@lang('misc.actions')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($user->emails()->get() as $email)
                <tr class="{{ $email->isVerified() ? '' : 'negative' }}">
                    <td>
                        {{ $email->email }}
                    </td>
                    <td>
                        @if(!$email->isVerified())
                            <a href="{{ route('account.emails.reverify', ['userId' => $user->id, 'emailId' => $email->id]) }}" class="ui button small secondary">@lang('pages.accountPage.email.resendVerify')</a>
                        @endif

                        <a href="{{ route('account.emails.delete', ['userId' => $user->id, 'emailId' => $email->id]) }}" class="ui button small basic">@lang('misc.delete')</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="full-width">
            <tr>
                <th colspan="2">
                    <a href="{{ route('account.emails.create', ['userId' => $user->id]) }}" class="ui button small primary">@lang('pages.accountPage.addEmail.title')</a>
                </th>
            </tr>
        </tfoot>
    </table>

    <a href="{{ route('account', ['userId' => $user->id]) }}"
            class="ui button basic">
        @lang('pages.accountPage.backToAccount')
    </a>
@endsection
