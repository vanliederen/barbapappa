@extends('layouts.app')

@section('title', __('pages.products.' . (empty(Request::input('q')) ? 'all' : 'search')))
@php
    $breadcrumbs = Breadcrumbs::generate('bar.product.index', $bar);
@endphp

@php
    use \App\Http\Controllers\CommunityController;

    // Define menulinks
    if(perms(CommunityController::permsManage()))
        $menulinks[] = [
            'name' => __('pages.products.manageProducts'),
            'link' => route('community.economy.product.index', [
                    'communityId' => $bar->community_id,
                    'economyId' => $bar->economy_id,
                ]),
            'icon' => 'edit',
        ];

    $menulinks[] = [
        'name' => __('pages.bar.backToBar'),
        'link' => route('bar.show', ['barId' => $bar->human_id]),
        'icon' => 'undo',
    ];
@endphp

@section('content')
    <h2 class="ui header">
        @yield('title') ({{ $products->count() }})

        <div class="sub header">
            in
            <a href="{{ route('bar.show', ['barId' => $bar->human_id]) }}">
                {{ $bar->name }}
            </a>
        </div>
    </h2>

    <div class="ui two item menu">
        <a href="{{ route('bar.show', ['barId' => $bar->human_id]) }}" class="item">@lang('pages.bar.buy.forMe')</a>
        <a href="{{ route('bar.buy', ['barId' => $bar->human_id]) }}" class="item">@lang('pages.bar.buy.forOthers')</a>
    </div>

    <div class="ui vertical menu fluid">
        {!! Form::open(['action' => ['BarProductController@index', $bar->human_id], 'method' => 'GET', 'class' => 'ui form']) !!}
            <div class="item">
                <div class="ui transparent icon input">
                    {{ Form::text('q', Request::input('q'), [
                        'placeholder' => __('pages.products.search') . '...',
                    ]) }}
                    <i class="icon link">
                        <span class="glyphicons glyphicons-search"></span>
                    </i>
                </div>
            </div>
        {!! Form::close() !!}

        @forelse($products as $product)
            <a href="{{ route('bar.product.show', [
                        'barId' => $bar->human_id,
                        'productId' => $product->id,
                    ]) }}"
                class="item">
                {{ $product->displayName() }}
                {!! $product->formatPrice($currencies, BALANCE_FORMAT_LABEL, ['neutral' => true]) !!}
            </a>
        @empty
            <i class="item">@lang('pages.products.noProducts')</i>
        @endforelse
    </div>

    <p>
        <a href="{{ route('bar.show', ['barId' => $bar->human_id]) }}"
                class="ui button basic">
            @lang('pages.bar.backToBar')
        </a>
    </p>
@endsection
