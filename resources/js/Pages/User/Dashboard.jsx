import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ auth, wallet, albumsCount, songsCount, pendingPayouts }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="min-h-screen bg-slate-950 px-4 py-6 text-white">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-8">
                        <h1 className="text-2xl font-bold">Welcome back, {auth.user.name}</h1>
                        <p className="mt-1 text-sm text-slate-400">
                            Manage your music publishing, royalties, payouts and takedown requests.
                        </p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl">
                            <p className="text-sm text-slate-400">Wallet Balance</p>
                            <h2 className="mt-3 text-3xl font-bold">
                                {Number(wallet.balance).toLocaleString()} {wallet.currency}
                            </h2>
                            <Link
                                href={route('user.wallet.index')}
                                className="mt-4 inline-block rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white"
                            >
                                View Wallet
                            </Link>
                        </div>

                        <div className="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl">
                            <p className="text-sm text-slate-400">Albums</p>
                            <h2 className="mt-3 text-3xl font-bold">{albumsCount}</h2>
                            <Link
                                href={route('user.albums.index')}
                                className="mt-4 inline-block rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-white"
                            >
                                View Albums
                            </Link>
                        </div>

                        <div className="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl">
                            <p className="text-sm text-slate-400">Songs</p>
                            <h2 className="mt-3 text-3xl font-bold">{songsCount}</h2>
                            <p className="mt-4 text-sm text-slate-500">Completed album songs only</p>
                        </div>

                        <div className="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-xl">
                            <p className="text-sm text-slate-400">Pending Payouts</p>
                            <h2 className="mt-3 text-3xl font-bold">{pendingPayouts}</h2>
                            <Link
                                href={route('user.wallet.index')}
                                className="mt-4 inline-block rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white"
                            >
                                Request Payout
                            </Link>
                        </div>
                    </div>

                    <div className="mt-8 grid gap-4 lg:grid-cols-3">
                        <Link
                            href={route('user.payment-methods.index')}
                            className="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10"
                        >
                            <h3 className="font-semibold">Payment Methods</h3>
                            <p className="mt-2 text-sm text-slate-400">
                                Add KBZ Bank, KBZ Pay or Wave Pay accounts.
                            </p>
                        </Link>

                        <Link
                            href={route('user.takedown-requests.index')}
                            className="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10"
                        >
                            <h3 className="font-semibold">Take Down Requests</h3>
                            <p className="mt-2 text-sm text-slate-400">
                                Request takedown for songs, albums or external links.
                            </p>
                        </Link>

                        <Link
                            href={route('user.profile.edit')}
                            className="rounded-3xl border border-white/10 bg-white/5 p-6 hover:bg-white/10"
                        >
                            <h3 className="font-semibold">Profile Settings</h3>
                            <p className="mt-2 text-sm text-slate-400">
                                Update your profile and view signed agreement PDF.
                            </p>
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}