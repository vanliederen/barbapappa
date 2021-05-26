@extends('layouts.app')

@section('title', __('pages.balanceImportMailBalance.title'))

@section('content')
    <h2 class="ui header">@yield('title')</h2>

    <p>@lang('pages.balanceImportMailBalance.description')</p>

    <br>

    {!! Form::open([
        'action' => [
            'BalanceImportEventController@doMailBalance',
            $community->human_id,
            $economy->id,
            $system->id,
            $event->id,
        ],
        'method' => 'POST',
        'class' => 'ui form'
    ]) !!}
        <div class="inline field {{ ErrorRenderer::hasError('mail_unregistered_users') ?  'error' : '' }}">
            <div class="ui checkbox">
                {{ Form::checkbox('mail_unregistered_users', true, true, ['tabindex' => 0, 'class' => 'hidden']) }}
                {{ Form::label('mail_unregistered_users', __('pages.balanceImportMailBalance.mailUnregisteredUsers')) }}
            </div>
            <br />
            {{ ErrorRenderer::inline('mail_unregistered_users') }}
        </div>

        <div class="inline field {{ ErrorRenderer::hasError('mail_not_joined_users') ?  'error' : '' }}">
            <div class="ui checkbox">
                {{ Form::checkbox('mail_not_joined_users', true, true, ['tabindex' => 0, 'class' => 'hidden']) }}
                {{ Form::label('mail_not_joined_users', __('pages.balanceImportMailBalance.mailNotJoinedUsers')) }}
            </div>
            <br />
            {{ ErrorRenderer::inline('mail_not_joined_users') }}
        </div>

        <div class="inline field {{ ErrorRenderer::hasError('mail_joined_users') ?  'error' : '' }}">
            <div class="ui checkbox">
                {{ Form::checkbox('mail_joined_users', true, true, ['tabindex' => 0, 'class' => 'hidden']) }}
                {{ Form::label('mail_joined_users', __('pages.balanceImportMailBalance.mailJoinedUsers')) }}
            </div>
            <br />
            {{ ErrorRenderer::inline('mail_joined_users') }}
        </div>

        <br>

        <div class="field {{ ErrorRenderer::hasError('message') ? 'error' : '' }}">
            {{ Form::label('message', __('pages.balanceImportMailBalance.extraMessage') . ' (' . __('general.optional') . '):') }}
            {{ Form::textarea('message', '', ['rows' => 3]) }}
            {{ ErrorRenderer::inline('message') }}
        </div>

        <div class="field {{ ErrorRenderer::hasError('invite_to_bar') ? 'error' : '' }}">
            {{ Form::label('bunq_account', __('pages.balanceImportMailBalance.inviteToJoinBar') . ':') }}

            <div class="ui fluid selection dropdown">
                {{ Form::hidden('invite_to_bar', 0) }}
                <i class="dropdown icon"></i>

                <div class="default text">@lang('misc.pleaseSpecify')</div>
                <div class="menu">
                    <div class="item" data-value="0">
                        <i>@lang('pages.balanceImportMailBalance.doNotInvite')</i>
                    </div>
                    {{-- TODO: only select joinable bars here --}}
                    @foreach($community->bars as $bar)
                        <div class="item" data-value="{{ $bar->id }}">
                            {{ $bar->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{ ErrorRenderer::inline('invite_to_bar') }}
        </div>

        @php
            // Create a locales map for the selection box
            $locales = [];
            foreach(langManager()->getLocales(true, false) as $entry)
                $locales[$entry] = __('lang.name', [], $entry);
        @endphp

        <div class="field {{ ErrorRenderer::hasError('language') ? 'error' : '' }}">
            {{ Form::label('language', __('lang.language') . ':') }}

            <div class="ui fluid selection dropdown">
                {{ Form::hidden('language', langManager()->getLocale()) }}
                <i class="dropdown icon"></i>

                <div class="default text">@lang('misc.unspecified')</div>
                <div class="menu">
                    @foreach($locales as $locale => $name)
                        <div class="item" data-value="{{ $locale }}">
                            <span class="{{ langManager()->getLocaleFlagClass($locale, false, true) }} flag"></span>
                            {{ $name }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{ ErrorRenderer::inline('language') }}
        </div>

        <br>

        <div class="ui divider"></div>

        {{-- Mail send confirmation checkbox --}}
        <div class="field {{ ErrorRenderer::hasError('confirm_send_mail') ? 'error' : '' }}">
            <div class="ui checkbox">
                {{ Form::checkbox('confirm_send_mail', true, false, ['tabindex' => 0, 'class' => 'hidden']) }}
                {{ Form::label('confirm_send_mail', __('pages.balanceImportMailBalance.confirmSendMessage')) }}
            </div>
            <br />
            {{ ErrorRenderer::inline('confirm_send_mail') }}
        </div>

        <br>

        <button class="ui button primary" type="submit" name="submit" value="">
            @lang('misc.send')
        </button>
        <a href="{{ route('community.economy.balanceimport.change.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                    'systemId' => $system->id,
                    'eventId' => $event->id,
                ]) }}"
                class="ui button basic">
            @lang('general.cancel')
        </a>

    {!! Form::close() !!}
@endsection