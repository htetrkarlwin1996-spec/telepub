import { Head, Link, usePage } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import {
    BanknotesIcon,
    MusicalNoteIcon,
    ArrowDownTrayIcon,
    ShieldCheckIcon,
    DocumentTextIcon,
    CheckBadgeIcon,
} from '@heroicons/react/24/outline';

export default function Dashboard({ stats, profile, agreement }) {
    const { auth } = usePage().props;

    const statCards = [
        {
            name: 'Wallet Balance',
            value: `${stats?.wallet_balance ?? '0.00'} ${stats?.currency ?? 'MMK'}`,
            description: 'Your available payout balance',
            icon: BanknotesIcon,
        },
        {
            name: 'Albums',
            value: stats?.albums_count ?? 0,
            description: 'Albums added by admin',
            icon: MusicalNoteIcon,
        },
        {
            name: 'Songs',
            value: stats?.songs_count ?? 0,
            description: 'Completed registration songs',
            icon: ShieldCheckIcon,
        },
        {
            name: 'Pending Payouts',
            value: stats?.pending_payouts_count ?? 0,
            description: 'Waiting admin approval',
            icon: ArrowDownTrayIcon,
        },
    ];

    return (
        <UserDashboardLayout
            title="Dashboard"
            subtitle="Manage publishing, albums, wallet and payouts."
        >
            <Head title="Dashboard" />

            <div className="overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-3xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                        <CheckBadgeIcon className="h-4 w-4" />
                        Account Verified
                    </div>

                    <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                        Welcome back, {auth?.user?.name}
                    </h1>

                    <p className="mt-4 text-sm leading-6 text-slate-300 sm:text-base">
                        Your music publishing account is ready. Admin will add albums and song registration data.
                        Completed albums will show full song information, BMI Work ID and IPI data.
                    </p>
                </div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((item) => (
                    <div
                        key={item.name}
                        className="rounded-3xl border border-white/10 bg-white/[0.04] p-5 shadow-xl"
                    >
                        <div className="rounded-2xl bg-white/10 p-3 w-fit">
                            <item.icon className="h-6 w-6 text-indigo-300" />
                        </div>

                        <p className="mt-5 text-sm text-slate-400">{item.name}</p>
                        <h3 className="mt-2 text-2xl font-bold text-white">{item.value}</h3>
                        <p className="mt-2 text-xs text-slate-500">{item.description}</p>
                    </div>
                ))}
            </div>

            <div className="mt-6 grid gap-4 lg:grid-cols-3">
                <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                    <div className="flex items-center gap-3">
                        <div className="rounded-2xl bg-indigo-500/20 p-3">
                            <DocumentTextIcon className="h-6 w-6 text-indigo-300" />
                        </div>
                        <div>
                            <h3 className="text-lg font-bold text-white">Agreement PDF</h3>
                            <p className="text-xs text-slate-500">
                                Signed publishing agreement
                            </p>
                        </div>
                    </div>

                    {agreement ? (
                        <>
                            <div className="mt-5 rounded-2xl bg-black/30 p-4">
                                <p className="text-sm font-semibold text-white">
                                    {agreement.agreement_name}
                                </p>
                                <p className="mt-1 text-xs text-slate-400">
                                    Signed by {agreement.signed_name}
                                </p>
                                <p className="mt-1 text-xs text-slate-500">
                                    Date: {agreement.signed_date}
                                </p>
                            </div>

                            <a
                                href={agreement.url}
                                target="_blank"
                                rel="noreferrer"
                                className="mt-5 inline-flex rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                            >
                                View Agreement PDF
                            </a>
                        </>
                    ) : (
                        <p className="mt-5 text-sm text-slate-400">
                            Agreement PDF is not generated yet.
                        </p>
                    )}
                </div>

                <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                    <h3 className="text-lg font-bold text-white">Profile Information</h3>

                    <div className="mt-5 space-y-3 text-sm">
                        <div className="flex justify-between gap-4">
                            <span className="text-slate-500">Display Name</span>
                            <span className="text-right text-white">
                                {profile?.display_name || auth?.user?.name}
                            </span>
                        </div>

                        <div className="flex justify-between gap-4">
                            <span className="text-slate-500">Artist Name</span>
                            <span className="text-right text-white">
                                {profile?.artist_name || 'Not added'}
                            </span>
                        </div>

                        <div className="flex justify-between gap-4">
                            <span className="text-slate-500">IPI Number</span>
                            <span className="text-right text-white">
                                {profile?.ipi_number || 'Optional'}
                            </span>
                        </div>
                    </div>

                    <Link
                        href={route('profile.edit')}
                        className="mt-5 inline-flex rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                    >
                        Edit Profile
                    </Link>
                </div>

                <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                    <h3 className="text-lg font-bold text-white">Next Actions</h3>

                    <div className="mt-5 space-y-3">
                        <button className="w-full rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white hover:bg-white/20">
                            Add Payment Method
                            <span className="mt-1 block text-xs font-normal text-slate-500">
                                KBZ Bank, KBZ Pay, Wave Pay
                            </span>
                        </button>

                        <button className="w-full rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white hover:bg-white/20">
                            Request Payout
                            <span className="mt-1 block text-xs font-normal text-slate-500">
                                Withdraw your available balance
                            </span>
                        </button>

                        <button className="w-full rounded-2xl bg-white/10 px-4 py-3 text-left text-sm font-semibold text-white hover:bg-white/20">
                            Create Take Down Request
                            <span className="mt-1 block text-xs font-normal text-slate-500">
                                Submit song, album and links
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div className="mt-6 rounded-3xl border border-white/10 bg-white/[0.04] p-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 className="text-lg font-bold text-white">Albums & Song Registration</h3>
                        <p className="mt-1 text-sm text-slate-400">
                            Albums will appear here after admin adds them to your account.
                        </p>
                    </div>

                    <div className="rounded-2xl bg-yellow-400/10 px-4 py-2 text-sm font-semibold text-yellow-300">
                        Waiting for admin data
                    </div>
                </div>

                <div className="mt-6 overflow-hidden rounded-2xl border border-white/10">
                    <div className="grid grid-cols-1 gap-0 divide-y divide-white/10 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                        <div className="p-5">
                            <p className="text-sm text-slate-500">Status</p>
                            <p className="mt-2 font-semibold text-white">Pending / Approved / Completed</p>
                        </div>

                        <div className="p-5">
                            <p className="text-sm text-slate-500">Completed Albums</p>
                            <p className="mt-2 font-semibold text-white">Full song data visible</p>
                        </div>

                        <div className="p-5">
                            <p className="text-sm text-slate-500">Song Data</p>
                            <p className="mt-2 font-semibold text-white">BMI Work ID + IPI Name</p>
                        </div>
                    </div>
                </div>
            </div>
        </UserDashboardLayout>
    );
}