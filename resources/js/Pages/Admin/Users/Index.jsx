import { Head, Link, router, useForm } from '@inertiajs/react';
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout';
import {
    UsersIcon,
    EyeIcon,
    MagnifyingGlassIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/react/24/outline';

function StatusBadge({ active }) {
    return active ? (
        <span className="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">
            <CheckCircleIcon className="h-4 w-4" />
            Active
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
            <XCircleIcon className="h-4 w-4" />
            Inactive
        </span>
    );
}

export default function Index({ users, filters, status }) {
    const { data, setData, get, processing } = useForm({
        search: filters?.search || '',
    });

    const search = (e) => {
        e.preventDefault();

        get(route('admin.users.index'), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const resetSearch = () => {
        router.get(route('admin.users.index'));
    };

    return (
        <AdminDashboardLayout
            title="Users Management"
            subtitle="Manage artists, profiles, wallets and agreements."
        >
            <Head title="Admin Users" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-red-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-red-400/20 bg-red-400/10 px-3 py-1 text-xs font-semibold text-red-300">
                            <UsersIcon className="h-4 w-4" />
                            User Control
                        </div>

                        <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                            Users Management
                        </h1>

                        <p className="mt-3 text-sm leading-6 text-slate-300">
                            View registered users, wallet balances, profiles and signed agreements.
                        </p>
                    </div>

                    <form onSubmit={search} className="flex w-full gap-3 lg:max-w-md">
                        <div className="relative flex-1">
                            <MagnifyingGlassIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />
                            <input
                                type="text"
                                value={data.search}
                                onChange={(e) => setData('search', e.target.value)}
                                placeholder="Search name or email"
                                className="w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-11 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-red-500 focus:ring-red-500"
                            />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-2xl bg-red-500 px-5 py-3 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60"
                        >
                            Search
                        </button>

                        {filters?.search && (
                            <button
                                type="button"
                                onClick={resetSearch}
                                className="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                            >
                                Reset
                            </button>
                        )}
                    </form>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="mt-6 overflow-hidden rounded-3xl border border-white/10 bg-white/[0.04] shadow-xl">
                <div className="hidden grid-cols-7 gap-4 border-b border-white/10 bg-white/[0.04] px-5 py-4 text-xs font-semibold uppercase tracking-wide text-slate-500 lg:grid">
                    <div className="col-span-2">User</div>
                    <div>Artist</div>
                    <div>Wallet</div>
                    <div>Status</div>
                    <div>Joined</div>
                    <div className="text-right">Action</div>
                </div>

                <div className="divide-y divide-white/10">
                    {users.data.length > 0 ? (
                        users.data.map((user) => (
                            <div
                                key={user.id}
                                className="grid gap-4 px-5 py-5 text-sm text-slate-300 lg:grid-cols-7 lg:items-center"
                            >
                                <div className="lg:col-span-2">
                                    <p className="font-semibold text-white">{user.name}</p>
                                    <p className="mt-1 text-xs text-slate-500">{user.email}</p>
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Artist</p>
                                    {user.artist_name || '-'}
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Wallet</p>
                                    <span className="font-semibold text-white">
                                        {user.wallet_balance} {user.currency}
                                    </span>
                                </div>

                                <div>
                                    <StatusBadge active={user.is_active} />
                                </div>

                                <div>
                                    <p className="lg:hidden text-xs text-slate-500">Joined</p>
                                    {user.created_at}
                                </div>

                                <div className="lg:text-right">
                                    <Link
                                        href={route('admin.users.show', user.id)}
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-400"
                                    >
                                        <EyeIcon className="h-4 w-4" />
                                        View
                                    </Link>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="p-10 text-center">
                            <UsersIcon className="mx-auto h-10 w-10 text-slate-600" />
                            <h3 className="mt-4 font-semibold text-white">No users found</h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Registered users will appear here.
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {users.links?.length > 3 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {users.links.map((link, index) => (
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