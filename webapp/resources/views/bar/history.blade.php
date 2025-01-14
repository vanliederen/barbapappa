@extends('layouts.app')

@section('title', __('pages.bar.purchases'))
@php
    $breadcrumbs = Breadcrumbs::generate('bar.history', $bar);
    $menusection = 'bar_manage';
@endphp

@section('content')
    <h2 class="ui header bar-header">
        @yield('title')
    </h2>

    <p>@lang('pages.bar.purchasesDescription')</p>

    <div class="ui hidden divider"></div>

    <div class="ui top vertical menu fluid">
        @forelse($productMutations as $productMutation)
            @php
                $self = barauth()->getUser()->id == $productMutation->mutation->owner_id;
            @endphp

            <a class="item"
                href="{{ route('transaction.show', [
                    'transactionId' => $productMutation->mutation->transaction_id,
                ]) }}">

                @if($productMutation->quantity != 1)
                    <span class="subtle">{{ $productMutation->quantity }}×</span>
                @endif

                {{ ($product = $productMutation->product) ?  $product->displayName() : __('pages.products.unknownProduct') }}
                {!! $productMutation->mutation->formatAmount(BALANCE_FORMAT_LABEL, [
                    'color' => $self,
                ]) !!}

                @if($productMutation->mutation->owner_id)
                    <span class="subtle">
                        @lang('misc.by') {{ $productMutation->mutation->owner->first_name }}
                    </span>
                @endif

                <span class="sub-label">
                    @include('includes.humanTimeDiff', ['time' => $productMutation->updated_at ?? $productMutation->created_at, 'short' => true])
                </span>

            </a>
        @empty
            <i class="item">@lang('pages.bar.noPurchases')...</i>
        @endforelse
    </div>

    {{ $productMutations->links() }}

    <p>
        <div class="ui floating right labeled icon dropdown button">
            <i class="dropdown icon"></i>
            @lang('misc.manage')
            <div class="menu">
                <a href="{{ route('bar.history.export', ['barId' => $bar->human_id]) }}"
                        class="item">
                    @lang('misc.export')
                </a>
            </div>
        </div>

        <a href="{{ route('bar.manage', ['barId' => $bar->human_id]) }}"
                class="ui button basic">
            @lang('pages.bar.backToBar')
        </a>
    </p>
@endsection
