@extends('layouts.app')

@section('title', $wallet->name)

@php
    // Define menulinks
    $menulinks[] = [
        'name' => __('general.goBack'),
        'link' => route('community.wallet.list', ['communityId' => $community->human_id, 'economyId' => $economy->id]),
        'icon' => 'undo',
    ];
    $menulinks[] = [
        'name' => __('misc.topUp'),
        'link' => route('community.wallet.topUp', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]),
        'icon' => 'credit-card',
    ];
    $menulinks[] = [
        'name' => __('pages.wallets.transfer'),
        'link' => route('community.wallet.transfer', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]),
        'icon' => 'transfer',
    ];
    $menulinks[] = [
        'name' => __('pages.transactions.title'),
        'link' => route('community.wallet.transactions', [
                'communityId' => $community->human_id,
                'economyId' => $economy->id,
                'walletId' => $wallet->id
            ]),
        'icon' => 'fees-payments',
    ];
@endphp

@section('content')
    <h2 class="ui header">
        @yield('title')

        <div class="sub header">
            @lang('misc.in')
            <a href="{{ route('community.wallet.index', ['communityId' => $community->human_id]) }}">
                {{ $community->name }}
            </a>
            @lang('misc.for')
            <a href="{{ route('community.wallet.list', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}">
                {{ $economy->name }}
            </a>
        </div>
    </h2>

    <div class="ui divider hidden"></div>

    <div class="ui one small statistics">
        <div class="statistic">
            <div class="value">
                {!! $wallet->formatBalance(BALANCE_FORMAT_COLOR) !!}
            </div>
            <div class="label">@lang('misc.balance')</div>
        </div>
    </div>

    <div class="ui divider hidden"></div>

    <center>
        <div class="ui buttons">
            <a href="{{ route('community.wallet.topUp', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]) }}"
                    class="ui button green">
                @lang('misc.pay')
            </a>
            <a href="{{ route('community.wallet.transfer', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]) }}"
                    class="ui button orange">
                @lang('pages.wallets.transfer')
            </a>
        </div>
    </center>

    <div class="ui divider hidden"></div>

    {{-- Transaction list --}}
    @include('transaction.include.list', [
        'groups' => [[
            'header' => trans_choice('pages.transactions.last#', count($transactions)),
            'transactions' => $transactions,
        ]],
        'button' => [
            'label' => __('misc.showAll'),
            'link' => route('community.wallet.transactions', [
                'communityId' => $community->human_id,
                'economyId' => $economy->id,
                'walletId' => $wallet->id,
            ]),
        ],
    ])

    <p>
        <div class="ui buttons">
            <a href="{{ route('community.wallet.edit', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]) }}"
                    class="ui button secondary">
                @lang('misc.rename')
            </a>
            <a href="{{ route('community.wallet.delete', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]) }}"
                    class="ui button negative">
                @lang('misc.delete')
            </a>
        </div>
        <a href="{{ route('community.wallet.stats', ['communityId' => $community->human_id, 'economyId' => $economy->id, 'walletId' => $wallet->id]) }}"
                class="ui button primary">
            @lang('misc.stats')
        </a>
    </p>

    <p>
        <a href="{{ route('community.wallet.list', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}"
                class="ui button basic">
            @lang('general.goBack')
        </a>
    </p>

    <div class="ui fluid accordion">
        <div class="title">
            <i class="dropdown icon"></i>
            @lang('misc.details')
        </div>
        <div class="content">
            <table class="ui compact celled definition table">
                <tbody>
                    <tr>
                        <td>@lang('misc.name')</td>
                        <td>{{ $wallet->name }}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.balance')</td>
                        <td>{!! $wallet->formatBalance(BALANCE_FORMAT_COLOR) !!}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.owner')</td>
                        <td>{{ $wallet->economyMember->name }}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.createdAt')</td>
                        <td>@include('includes.humanTimeDiff', ['time' => $wallet->created_at])</td>
                    </tr>
                    @if($wallet->created_at != $wallet->updated_at)
                        <tr>
                            <td>@lang('misc.lastChanged')</td>
                            <td>@include('includes.humanTimeDiff', ['time' => $wallet->updated_at])</td>
                        </tr>
                    @endif
                    <tr>
                        <td>@lang('misc.reference')</td>
                        <td><code class="literal">wallet#{{ $wallet->id }}</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
