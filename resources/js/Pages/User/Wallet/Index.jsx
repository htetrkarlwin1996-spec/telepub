import { Head, useForm } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import {
    BanknotesIcon,
    ArrowDownTrayIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    CreditCardIcon,
} from '@heroicons/react/24/outline';

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        approved: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        rejected: 'bg-red-400/10 text-red-300 border-red-400/20',
        paid: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
    };

    return (
        <span className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${classes[status] || classes.pending}`}>
            {status}
        </span>
    );
}

function StatCard({ title, value, subtitle, icon: Icon }) {
    return (
        <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-5 shadow-xl">
            <div className="flex items-center justify-between">
                <div className="rounded-2xl bg-white/10 p-3">
                    <Icon className="h-6 w-6 text-indigo-300" />
                </div>
            </div>

            <p className="mt-5 text-sm text-slate-400">{title}</p>
            <h3 className="mt-2 text-2xl font-bold text-white">{value}</h3>
            <p className="mt-2 text-xs text-slate-500">{subtitle}</p>
        </div>
    );
}

export default function Index({ wallet, paymentMethods = [], payoutRequests = [], status }) {
    const defaultMethod = paymentMethods.find((method) => method.is_default) || paymentMethods[0];

    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        amount: '',
        payment_method_id: defaultMethod?.id || '',
        user_note: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('user.wallet.payout-request'), {
            preserveScroll: true,
            onSuccess: () => {
                reset('amount', 'user_note');
            },
        });
    };

    return (
        <UserDashboardLayout
            title="Wallet & Payouts"
            subtitle="View wallet balance and request payouts."
        >
            <Head title="Wallet & Payouts" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-emerald-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-3xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                        <BanknotesIcon className="h-4 w-4" />
                        Wallet Balance
                    </div>

                    <h1 className="mt-4 text-3xl font-bold text-white sm:text-5xl">
                        {wallet?.balance || '0.00'} {wallet?.currency || 'MMK'}
                    </h1>

                    <p className="mt-4 text-sm leading-6 text-slate-300">
                        You can request payout using your saved default payment method.
                    </p>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            {recentlySuccessful && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    Payout request submitted successfully.
                </div>
            )}

            <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Available Balance"
                    value={`${wallet?.balance || '0.00'} ${wallet?.currency || 'MMK'}`}
                    subtitle="Current withdrawable amount"
                    icon={BanknotesIcon}
                />

                <StatCard
                    title="Total Earned"
                    value={`${wallet?.total_earned || '0.00'} ${wallet?.currency || 'MMK'}`}
                    subtitle="All time earnings"
                    icon={CheckCircleIcon}
                />

                <StatCard
                    title="Total Withdrawn"
                    value={`${wallet?.total_withdrawn || '0.00'} ${wallet?.currency || 'MMK'}`}
                    subtitle="Paid payout total"
                    icon={ArrowDownTrayIcon}
                />

                <StatCard
                    title="Payout Requests"
                    value={payoutRequests.length}
                    subtitle="Total payout request history"
                    icon={ClockIcon}
                />
            </div>

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-1">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Request Payout</h3>
                        <p className="mt-1 text-sm text-slate-400">
                            Select payment method and enter amount.
                        </p>

                        {paymentMethods.length === 0 ? (
                            <div className="mt-6 rounded-2xl bg-yellow-400/10 p-4 text-sm text-yellow-300">
                                Please add a payment method first before requesting payout.
                            </div>
                        ) : (
                            <form onSubmit={submit} className="mt-6 space-y-5">
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-300">
                                        Amount
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        step="0.01"
                                        value={data.amount}
                                        onChange={(e) => setData('amount', e.target.value)}
                                        placeholder="Enter amount"
                                        className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.amount && (
                                        <p className="mt-2 text-sm text-red-400">{errors.amount}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-300">
                                        Payment Method
                                    </label>
                                    <select
                                        value={data.payment_method_id}
                                        onChange={(e) => setData('payment_method_id', e.target.value)}
                                        className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select method</option>
                                        {paymentMethods.map((method) => (
                                            <option key={method.id} value={method.id}>
                                                {method.type_label} - {method.account_name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.payment_method_id && (
                                        <p className="mt-2 text-sm text-red-400">{errors.payment_method_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-300">
                                        Note Optional
                                    </label>
                                    <textarea
                                        rows="4"
                                        value={data.user_note}
                                        onChange={(e) => setData('user_note', e.target.value)}
                                        placeholder="Any note for admin"
                                        className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    {errors.user_note && (
                                        <p className="mt-2 text-sm text-red-400">{errors.user_note}</p>
                                    )}
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-60"
                                >
                                    {processing ? 'Submitting...' : 'Submit Payout Request'}
                                </button>
                            </form>
                        )}
                    </div>
                </div>

                <div className="lg:col-span-2">
                    <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
                        <h3 className="text-lg font-bold text-white">Payout History</h3>
                        <p className="mt-1 text-sm text-slate-400">
                            Your payout request records and admin status.
                        </p>

                        <div className="mt-6 space-y-4">
                            {payoutRequests.length > 0 ? (
                                payoutRequests.map((payout) => (
                                    <div
                                        key={payout.id}
                                        className="rounded-2xl border border-white/10 bg-black/30 p-4"
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h4 className="text-lg font-bold text-white">
                                                        {payout.amount} {payout.currency}
                                                    </h4>
                                                    <StatusBadge status={payout.status} />
                                                </div>

                                                <p className="mt-1 text-sm text-slate-500">
                                                    Requested at {payout.created_at}
                                                </p>
                                            </div>

                                            {payout.paid_at && (
                                                <p className="text-sm text-emerald-300">
                                                    Paid at {payout.paid_at}
                                                </p>
                                            )}
                                        </div>

                                        {payout.payment_method && (
                                            <div className="mt-4 rounded-xl bg-white/[0.04] p-3 text-sm">
                                                <div className="flex items-center gap-2 text-slate-300">
                                                    <CreditCardIcon className="h-4 w-4" />
                                                    <span>{payout.payment_method.type_label}</span>
                                                </div>

                                                <p className="mt-2 text-slate-500">
                                                    Account: {payout.payment_method.account_name}
                                                </p>
                                            </div>
                                        )}

                                        {payout.user_note && (
                                            <p className="mt-3 text-sm text-slate-400">
                                                User Note: {payout.user_note}
                                            </p>
                                        )}

                                        {payout.admin_note && (
                                            <p className="mt-3 text-sm text-yellow-300">
                                                Admin Note: {payout.admin_note}
                                            </p>
                                        )}
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-white/10 p-8 text-center">
                                    <XCircleIcon className="mx-auto h-10 w-10 text-slate-600" />
                                    <h4 className="mt-4 font-semibold text-white">
                                        No payout requests yet
                                    </h4>
                                    <p className="mt-2 text-sm text-slate-500">
                                        Your request history will appear here.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </UserDashboardLayout>
    );
}