@extends('layouts.app')

@section('title', $economy->name)

@php
    use \App\Http\Controllers\EconomyController;
    use \App\Http\Controllers\CurrencyController;

    // Define menulinks
    $menulinks[] = [
        'name' => __('pages.economies.backToEconomies'),
        'link' => route('community.economy.index', ['communityId' => $community->human_id]),
        'icon' => 'undo',
    ];
    $menulinks[] = [
        'name' => __('pages.currencies.manage'),
        'link' => route('community.economy.currency.index', [
            'communityId' => $community->human_id,
            'economyId' => $economy->id
        ]),
        'icon' => 'currency-conversion',
    ];
    $menulinks[] = [
        'name' => __('pages.products.manageProducts'),
        'link' => route('community.economy.product.index', [
            'communityId' => $community->human_id,
            'economyId' => $economy->id
        ]),
        'icon' => 'shopping-bag',
    ];
    $menulinks[] = [
        'name' => __('pages.paymentService.manageServices'),
        'link' => route('community.economy.payservice.index', [
            'communityId' => $community->human_id,
            'economyId' => $economy->id
        ]),
        'icon' => 'credit-card',
    ];
    $menulinks[] = [
        'name' => __('pages.balanceImport.manageSystems'),
        'link' => route('community.economy.balanceimport.index', [
            'communityId' => $community->human_id,
            'economyId' => $economy->id
        ]),
        'icon' => 'file-import',
    ];
    $menulinks[] = [
        'name' => __('pages.finance.title'),
        'link' => route('community.economy.finance.overview', [
            'communityId' => $community->human_id,
            'economyId' => $economy->id
        ]),
        'icon' => 'charts',
    ];
@endphp

@section('content')
    <h2 class="ui header">
        @yield('title')

        <div class="sub header">
            @lang('misc.in')
            <a href="{{ route('community.manage', ['communityId' => $community->human_id]) }}">
                {{ $community->name }}
            </a>
        </div>
    </h2>

    @if(perms(EconomyController::permsManage()))
        <div class="ui vertical menu fluid">
            <h5 class="ui item header">@lang('pages.community.economy')</h5>
            <a href="{{ route('community.economy.edit', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}"
                    class="item">
                @lang('pages.economies.editEconomy')
            </a>
            <a href="{{ route('community.economy.delete', ['communityId' => $community->human_id, 'economyId' => $economy->id]) }}"
                    class="item">
                @lang('pages.economies.deleteEconomy')
            </a>
        </div>
    @endif

    <div class="ui vertical menu fluid">
        <h5 class="ui item header">@lang('misc.assets')</h5>
        <a href="{{ route('community.economy.product.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id
                ]) }}"
                class="item">
            @lang('pages.products.title')
        </a>
        <a href="{{ route('community.economy.payservice.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id
                ]) }}"
                class="item">
            @lang('pages.paymentService.title')
        </a>

        <a href="{{ route('community.economy.balanceimport.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id
                ]) }}"
                class="item">
            @lang('pages.balanceImport.title')
        </a>

        <a href="{{ route('community.economy.finance.overview', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id
                ]) }}"
                class="item">
            @lang('pages.finance.title')
        </a>
    </div>

    @if(perms(CurrencyController::permsView()))
        @include('community.economy.include.currencyList', [
            'header' => __('misc.currencies') . ' (' .  $currencies->count() . ')',
            'currencies' => $currencies,
            'button' => [
                'label' => __('pages.currencies.manage'),
                'link' => route('community.economy.currency.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id
                ]),
            ],
        ])
    @endif

    <p>
        <a href="{{ route('community.economy.index', ['communityId' => $community->human_id]) }}"
                class="ui button basic">
            @lang('pages.economies.backToEconomies')
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
                        <td>{{ $economy->name }}</td>
                    </tr>
                    <tr>
                        <td>@lang('misc.createdAt')</td>
                        <td>@include('includes.humanTimeDiff', ['time' => $economy->created_at])</td>
                    </tr>
                    @if($economy->created_at != $economy->updated_at)
                        <tr>
                            <td>@lang('misc.lastChanged')</td>
                            <td>@include('includes.humanTimeDiff', ['time' => $economy->updated_at])</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
