import { Head, Link } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import {
    UsersIcon,
    MusicalNoteIcon,
    BanknotesIcon,
    ArrowDownTrayIcon,
    ClockIcon,
    CheckCircleIcon,
    PaperAirplaneIcon,
    ChartBarIcon,
    BuildingLibraryIcon,
} from '@heroicons/react/24/outline';

function StatCard({ title, value, subtitle, icon: Icon, href = '#', tone = 'red' }) {
    const tones = {
        red: 'text-red-300',
        emerald: 'text-emerald-300',
        yellow: 'text-yellow-300',
        blue: 'text-blue-300',
        purple: 'text-purple-300',
    };

    return (
        <Link
            href={href}
            className="rounded-3xl border border-white/10 bg-white/[0.04] p-5 shadow-xl transition hover:bg-white/[0.08]"
        >
            <div className="w-fit rounded-2xl bg-white/10 p-3">
                <Icon className={`h-6 w-6 ${tones[tone] || tones.red}`} />
            </div>

            <p className="mt-5 text-sm text-slate-400">{title}</p>

            <h3 className="mt-2 break-words text-2xl font-bold text-white sm:text-3xl">
                {value}
            </h3>

            <p className="mt-2 text-xs leading-5 text-slate-500">{subtitle}</p>
        </Link>
    );
}

function MoneyCard({ title, value, subtitle, icon: Icon, tone = 'emerald' }) {
    const tones = {
        emerald: 'text-emerald-300 bg-emerald-400/10 border-emerald-400/20',
        red: 'text-red-300 bg-red-400/10 border-red-400/20',
        yellow: 'text-yellow-300 bg-yellow-400/10 border-yellow-400/20',
        blue: 'text-blue-300 bg-blue-400/10 border-blue-400/20',
        purple: 'text-purple-300 bg-purple-400/10 border-purple-400/20',
    };

    return (
        <div className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl">
            <div
                className={`w-fit rounded-2xl border p-3 ${
                    tones[tone] || tones.emerald
                }`}
            >
                <Icon className="h-6 w-6" />
            </div>

            <p className="mt-5 text-sm text-slate-400">{title}</p>

            <h3 className="mt-2 break-words text-2xl font-bold text-white sm:text-3xl">
                {value}
            </h3>

            <p className="mt-2 text-xs leading-5 text-slate-500">{subtitle}</p>
        </div>
    );
}

export default function Dashboard({ stats }) {
    const currency = stats?.currency ?? 'MMK';

    return (
        <AdminDashboardLayout
            title="Admin Dashboard"
            subtitle="Control users, albums, payouts and takedown requests."
        >
            <Head title="Admin Dashboard" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="max-w-4xl">
                    <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                        Admin Control Center
                    </div>

                    <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                        Music Publishing Admin Panel
                    </h1>

                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        Manage artists, albums, song registrations, wallet payouts,
                        registration fees and take down requests from one place.
                    </p>
                </div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <MoneyCard
                    title="Initial Capital"
                    value={`${stats?.initial_capital ?? '0.00'} ${currency}`}
                    subtitle="Business base investment / initial capital"
                    icon={BuildingLibraryIcon}
                    tone="blue"
                />

                <MoneyCard
                    title="Total Registration Amount"
                    value={`${stats?.total_registration_amount ?? '0.00'} ${currency}`}
                    subtitle="Total album registration fees collected"
                    icon={BanknotesIcon}
                    tone="emerald"
                />

                <MoneyCard
                    title="Paid Payout Amount"
                    value={`${stats?.paid_payout_amount ?? '0.00'} ${currency}`}
                    subtitle="Total payouts already marked as paid"
                    icon={PaperAirplaneIcon}
                    tone="red"
                />

                <MoneyCard
                    title="Net Position"
                    value={`${stats?.net_position ?? '0.00'} ${currency}`}
                    subtitle="Initial capital + registration fees - paid payouts"
                    icon={ChartBarIcon}
                    tone="purple"
                />
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <StatCard
                    title="Users"
                    value={stats?.users_count ?? 0}
                    subtitle="Registered user accounts"
                    icon={UsersIcon}
                    href={route('admin.users.index')}
                    tone="red"
                />

                <StatCard
                    title="Albums"
                    value={stats?.albums_count ?? 0}
                    subtitle="Total albums in system"
                    icon={MusicalNoteIcon}
                    href={route('admin.albums.index')}
                    tone="red"
                />

                <StatCard
                    title="Pending Albums"
                    value={stats?.pending_albums_count ?? 0}
                    subtitle="Waiting for BMI Work ID / IPI Name"
                    icon={ClockIcon}
                    href={route('admin.albums.index')}
                    tone="yellow"
                />

                <StatCard
                    title="Completed Albums"
                    value={stats?.completed_albums_count ?? 0}
                    subtitle="Albums with BMI Work ID available"
                    icon={CheckCircleIcon}
                    href={route('admin.albums.index')}
                    tone="emerald"
                />

                <StatCard
                    title="Delivery Albums"
                    value={stats?.delivery_albums_count ?? 0}
                    subtitle="Albums with IPI Name delivered"
                    icon={PaperAirplaneIcon}
                    href={route('admin.albums.index')}
                    tone="purple"
                />

                <StatCard
                    title="Pending Payouts"
                    value={stats?.pending_payouts_count ?? 0}
                    subtitle={`Pending payout amount: ${stats?.pending_payout_amount ?? '0.00'} ${currency}`}
                    icon={BanknotesIcon}
                    href={route('admin.payouts.index')}
                    tone="blue"
                />

                <StatCard
                    title="Approved Payouts"
                    value={stats?.approved_payouts_count ?? 0}
                    subtitle="Approved but not marked as paid yet"
                    icon={CheckCircleIcon}
                    href={route('admin.payouts.index')}
                    tone="emerald"
                />

                <StatCard
                    title="Paid Payouts"
                    value={stats?.paid_payouts_count ?? 0}
                    subtitle="Completed payout transactions"
                    icon={PaperAirplaneIcon}
                    href={route('admin.payouts.index')}
                    tone="purple"
                />

                <StatCard
                    title="Pending Take Downs"
                    value={stats?.pending_takedowns_count ?? 0}
                    subtitle="DMCA / take down requests waiting review"
                    icon={ArrowDownTrayIcon}
                    href={route('admin.takedowns.index')}
                    tone="yellow"
                />
            </div>
        </AdminDashboardLayout>
    );
}