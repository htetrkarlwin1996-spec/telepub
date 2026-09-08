import { Head, router } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import {
    BanknotesIcon,
    CheckCircleIcon,
    XCircleIcon,
    PaperAirplaneIcon,
    CreditCardIcon,
} from '@heroicons/react/24/outline';

function StatusBadge({ status }) {
    const classes = {
        pending: 'bg-yellow-400/10 text-yellow-300 border-yellow-400/20',
        approved: 'bg-blue-400/10 text-blue-300 border-blue-400/20',
        rejected: 'bg-red-400/10 text-red-300 border-red-400/20',
        paid: 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
    };

    const labels = {
        pending: 'Pending',
        approved: 'Approved',
        rejected: 'Rejected',
        paid: 'Paid',
    };

    return (
        <span
            className={`inline-flex rounded-full border px-3 py-1 text-xs font-semibold ${
                classes[status] || classes.pending
            }`}
        >
            {labels[status] || status}
        </span>
    );
}

export default function Index({ payouts, status }) {
    const approve = (payout) => {
        if (!payout?.id) {
            alert('Payout ID missing.');
            return;
        }

        if (
            !confirm(
                `Approve payout ${payout.amount} ${payout.currency}? Wallet balance will be deducted.`
            )
        ) {
            return;
        }

        router.patch(
            `/admin/payouts/${payout.id}/approve`,
            {},
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.log(errors);
                    alert('Approve failed. Please check wallet balance or logs.');
                },
            }
        );
    };

    const markPaid = (payout) => {
        if (!payout?.id) {
            alert('Payout ID missing.');
            return;
        }

        if (
            !confirm(
                `Mark this payout as paid and send email to ${payout.user.email}?`
            )
        ) {
            return;
        }

        router.patch(
            `/admin/payouts/${payout.id}/paid`,
            {
                admin_note: 'Payment transferred successfully.',
            },
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.log(errors);
                    alert('Mark paid failed. Please check mail settings or logs.');
                },
            }
        );
    };

    const reject = (payout) => {
        if (!payout?.id) {
            alert('Payout ID missing.');
            return;
        }

        const note = prompt('Reject reason / admin note:', 'Rejected by admin.');

        if (note === null) {
            return;
        }

        router.patch(
            `/admin/payouts/${payout.id}/reject`,
            {
                admin_note: note,
            },
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.log(errors);
                    alert('Reject failed. Please check logs.');
                },
            }
        );
    };

    return (
        <AdminDashboardLayout
            title="Payout Requests"
            subtitle="Approve payouts, deduct wallet balance and mark payments as paid."
        >
            <Head title="Admin Payouts" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-3xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                        <BanknotesIcon className="h-4 w-4" />
                        Payout Control
                    </div>

                    <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                        Payout Requests
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        Approve user payout requests first. After real transfer is completed,
                        mark as paid to send email notification.
                    </p>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="mt-6 space-y-4">
                {payouts?.data?.length > 0 ? (
                    payouts.data.map((payout) => (
                        <div
                            key={payout.id}
                            className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl"
                        >
                            <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div>
                                    <div className="flex flex-wrap items-center gap-3">
                                        <h3 className="text-2xl font-bold text-white">
                                            {payout.amount} {payout.currency}
                                        </h3>

                                        <StatusBadge status={payout.status} />
                                    </div>

                                    <p className="mt-2 text-sm text-slate-400">
                                        Requested by {payout.user.name} — {payout.user.email}
                                    </p>

                                    <p className="mt-1 text-xs text-slate-500">
                                        Requested at {payout.created_at}
                                    </p>
                                </div>

                                <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                    {payout.status === 'pending' && (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => approve(payout)}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-500 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-400"
                                            >
                                                <CheckCircleIcon className="h-4 w-4" />
                                                Approve
                                            </button>

                                            <button
                                                type="button"
                                                onClick={() => reject(payout)}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 hover:bg-red-500/20"
                                            >
                                                <XCircleIcon className="h-4 w-4" />
                                                Reject
                                            </button>
                                        </>
                                    )}

                                    {payout.status === 'approved' && (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => markPaid(payout)}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-400"
                                            >
                                                <PaperAirplaneIcon className="h-4 w-4" />
                                                Mark Paid + Email
                                            </button>

                                            <button
                                                type="button"
                                                onClick={() => reject(payout)}
                                                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 hover:bg-red-500/20"
                                            >
                                                <XCircleIcon className="h-4 w-4" />
                                                Reject / Refund
                                            </button>
                                        </>
                                    )}

                                    {payout.status === 'paid' && (
                                        <div className="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-300">
                                            <CheckCircleIcon className="h-4 w-4" />
                                            Paid Completed
                                        </div>
                                    )}

                                    {payout.status === 'rejected' && (
                                        <div className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300">
                                            <XCircleIcon className="h-4 w-4" />
                                            Rejected
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="mt-6 grid gap-4 lg:grid-cols-3">
                                <div className="rounded-2xl bg-black/30 p-4">
                                    <p className="text-xs text-slate-500">User</p>

                                    <p className="mt-1 text-sm font-semibold text-white">
                                        {payout.user.name}
                                    </p>

                                    <p className="mt-1 text-xs text-slate-500">
                                        {payout.user.email}
                                    </p>
                                </div>

                                <div className="rounded-2xl bg-black/30 p-4">
                                    <div className="flex items-center gap-2">
                                        <CreditCardIcon className="h-4 w-4 text-red-300" />
                                        <p className="text-xs text-slate-500">Payment Method</p>
                                    </div>

                                    {payout.payment_method ? (
                                        <>
                                            <p className="mt-2 text-sm font-semibold text-white">
                                                {payout.payment_method.type_label}
                                            </p>

                                            {payout.payment_method.bank_name && (
                                                <p className="mt-1 text-xs text-slate-500">
                                                    Bank: {payout.payment_method.bank_name}
                                                </p>
                                            )}

                                            <p className="mt-1 text-xs text-slate-500">
                                                Account: {payout.payment_method.account_name}
                                            </p>

                                            <p className="mt-1 text-xs text-slate-500">
                                                {payout.payment_method.account_number ||
                                                    payout.payment_method.phone_number ||
                                                    '-'}
                                            </p>
                                        </>
                                    ) : (
                                        <p className="mt-2 text-sm text-slate-500">
                                            No payment method
                                        </p>
                                    )}
                                </div>

                                <div className="rounded-2xl bg-black/30 p-4">
                                    <p className="text-xs text-slate-500">Paid At</p>

                                    <p className="mt-1 text-sm font-semibold text-white">
                                        {payout.paid_at || '-'}
                                    </p>

                                    {payout.admin_note && (
                                        <p className="mt-2 text-xs text-yellow-300">
                                            Note: {payout.admin_note}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {payout.user_note && (
                                <div className="mt-4 rounded-2xl bg-white/[0.04] p-4">
                                    <p className="text-xs text-slate-500">User Note</p>

                                    <p className="mt-2 whitespace-pre-wrap text-sm text-slate-300">
                                        {payout.user_note}
                                    </p>
                                </div>
                            )}
                        </div>
                    ))
                ) : (
                    <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.04] p-10 text-center">
                        <BanknotesIcon className="mx-auto h-10 w-10 text-slate-600" />

                        <h3 className="mt-4 font-semibold text-white">
                            No payout requests yet
                        </h3>

                        <p className="mt-2 text-sm text-slate-500">
                            User payout requests will appear here.
                        </p>
                    </div>
                )}
            </div>

            {payouts?.links?.length > 3 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {payouts.links.map((link, index) => (
                        <button
                            key={index}
                            type="button"
                            disabled={!link.url}
                            onClick={() => link.url && router.visit(link.url)}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            className={[
                                'rounded-xl px-4 py-2 text-sm font-semibold',
                                link.active
                                    ? 'bg-red-500 text-white'
                                    : 'bg-white/10 text-slate-300 hover:bg-white/20',
                                !link.url ? 'cursor-not-allowed opacity-50' : '',
                            ].join(' ')}
                        />
                    ))}
                </div>
            )}
        </AdminDashboardLayout>
    );
}