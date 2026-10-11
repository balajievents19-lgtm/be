@php
    $summary = $this->profitSummary();
@endphp
<div class="rounded-xl border border-gray-200 bg-white p-4 text-sm dark:border-gray-700 dark:bg-gray-900">
    <h3 class="mb-3 font-semibold">Internal profit summary</h3>
    <p class="mb-3 text-xs text-gray-500">Net-revenue basis: tax is excluded from profit. Customer PDF never includes these figures.</p>
    <dl class="grid grid-cols-2 gap-2 md:grid-cols-3">
        <div>Client amount (grand total): <strong>INR {{ $summary['client_amount'] }}</strong></div>
        <div>Client net (minus tax): <strong>INR {{ $summary['client_net_amount'] }}</strong></div>
        <div>Total actual item cost: <strong>INR {{ $summary['total_actual_item_cost'] }}</strong></div>
        <div>Additional direct expenses: <strong>INR {{ $summary['additional_direct_expenses'] }}</strong></div>
        <div>Total actual cost: <strong>INR {{ $summary['total_actual_cost'] }}</strong></div>
        <div>Profit: <strong>INR {{ $summary['profit'] }}</strong></div>
        <div>Profit margin: <strong>{{ $summary['profit_margin_percent'] === null ? 'n/a (zero net amount)' : $summary['profit_margin_percent'].'%' }}</strong></div>
    </dl>
</div>
