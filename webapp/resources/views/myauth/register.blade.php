@extends('layouts.app')

@section('title', __('auth.register'))

@section('content')
    <h2 class="ui header">@yield('title')</h2>

    {!! Form::open(['action' => ['RegisterController@doRegister'], 'method' => 'POST', 'class' => 'ui form']) !!}

    <div class="field {{ ErrorRenderer::hasError('email') ? 'error' : '' }}">
        {{ Form::label('email', __('account.email') . ':') }}
        {{ Form::text('email', '', ['placeholder' => __('account.emailPlaceholder')]) }}
        {{ ErrorRenderer::inline('email') }}
    </div>

    <div class="two fields">
        <div class="field {{ ErrorRenderer::hasError('first_name') ? 'error' : '' }}">
            {{ Form::label('first_name', __('account.firstName') . ':') }}
            {{ Form::text('first_name', '', ['placeholder' => __('account.firstNamePlaceholder')]) }}
            {{ ErrorRenderer::inline('first_name') }}
        </div>

        <div class="field {{ ErrorRenderer::hasError('last_name') ? 'error' : '' }}">
            {{ Form::label('last_name', __('account.lastName') . ':') }}
            {{ Form::text('last_name', '', ['placeholder' => __('account.lastNamePlaceholder')]) }}
            {{ ErrorRenderer::inline('last_name') }}
        </div>
    </div>

    <div class="two fields">
        <div class="field {{ ErrorRenderer::hasError('password') ? 'error' : '' }}">
            {{ Form::label('password', __('account.password') . ':') }}
            {{ Form::password('password') }}
            {{ ErrorRenderer::inline('password') }}
        </div>

        <div class="field {{ ErrorRenderer::hasError('password_confirmation') ? 'error' : '' }}">
            {{ Form::label('password_confirmation', __('account.confirmPassword') . ':') }}
            {{ Form::password('password_confirmation') }}
            {{ ErrorRenderer::inline('password_confirmation') }}
        </div>
    </div>

    <br />

    <div class="inline field {{ ErrorRenderer::hasError('accept_terms') ? 'error' : '' }}">
        <div class="ui checkbox">
            <input type="checkbox" name="accept_terms" tabindex="0" class="hidden">
            <label for="accept_terms">@lang('auth.iAgreeToTerms', ['terms' => route('terms'), 'privacy' => route('privacy')])</label>
        </div>
        <br>
        {{ ErrorRenderer::inline('accept_terms') }}
    </div>

    <br />

    <div>
        <button class="ui button primary" type="submit">@lang('auth.register')</button>
        <a href="{{ route('login') }}" class="ui button basic">@lang('auth.login')</a>
    </div>

    {!! Form::close() !!}
@endsection
