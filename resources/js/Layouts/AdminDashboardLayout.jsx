import { Link, usePage } from '@inertiajs/react';
import { Dialog, DialogPanel } from '@headlessui/react';
import { useState } from 'react';
import {
    Bars3Icon,
    XMarkIcon,
    Squares2X2Icon,
    UsersIcon,
    MusicalNoteIcon,
    BanknotesIcon,
    ArrowDownTrayIcon,
    UserCircleIcon,
} from '@heroicons/react/24/outline';

const navigation = [
    { name: 'Dashboard', routeName: 'admin.dashboard', href: route('admin.dashboard'), icon: Squares2X2Icon },
    { name: 'Users', routeName: 'admin.users.index', href: route('admin.users.index'), icon: UsersIcon },
    { name: 'Albums', routeName: 'admin.albums.index', href: route('admin.albums.index'), icon: MusicalNoteIcon },
    { name: 'Payouts', routeName: 'admin.payouts.index', href: route('admin.payouts.index'), icon: BanknotesIcon },
    { name: 'Take Downs', routeName: 'admin.takedowns.index', href: route('admin.takedowns.index'), icon: ArrowDownTrayIcon },
];

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

function isActiveRoute(item) {
    try {
        return route().current(item.routeName);
    } catch {
        return false;
    }
}

export default function AdminDashboardLayout({
    title = 'Admin Dashboard',
    subtitle = 'Manage your music publishing system.',
    children,
}) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const SidebarContent = () => (
        <div className="flex h-full flex-col bg-black">
            <div className="flex h-16 items-center border-b border-white/10 px-6">
                <div>
                    <h1 className="text-lg font-bold text-white">Tele Music</h1>
                    <p className="text-xs text-red-300">Admin Panel</p>
                </div>
            </div>

            <nav className="flex-1 space-y-1 px-4 py-6">
                {navigation.map((item) => {
                    const active = isActiveRoute(item);

                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={classNames(
                                active
                                    ? 'bg-red-500 text-white shadow-lg shadow-red-500/20'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white',
                                'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition'
                            )}
                        >
                            <item.icon className="h-5 w-5" />
                            {item.name}
                        </Link>
                    );
                })}
            </nav>

            <div className="border-t border-white/10 p-4">
                <div className="rounded-2xl bg-white/5 p-4">
                    <p className="text-sm font-semibold text-white">{auth?.user?.name}</p>
                    <p className="mt-1 truncate text-xs text-slate-400">{auth?.user?.email}</p>

                    <div className="mt-3 inline-flex rounded-full bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-300">
                        Admin
                    </div>

                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="mt-4 w-full rounded-xl bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-300 hover:bg-red-500/20"
                    >
                        Logout
                    </Link>
                </div>
            </div>
        </div>
    );

    return (
        <div className="min-h-screen bg-slate-950">
            <Dialog open={sidebarOpen} onClose={setSidebarOpen} className="relative z-50 lg:hidden">
                <div className="fixed inset-0 bg-black/70" />

                <div className="fixed inset-0 flex">
                    <DialogPanel className="relative flex w-full max-w-xs flex-1">
                        <SidebarContent />

                        <button
                            type="button"
                            onClick={() => setSidebarOpen(false)}
                            className="absolute left-full top-4 ml-3 rounded-full bg-white/10 p-2 text-white"
                        >
                            <XMarkIcon className="h-5 w-5" />
                        </button>
                    </DialogPanel>
                </div>
            </Dialog>

            <div className="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-72 lg:flex-col">
                <SidebarContent />
            </div>

            <div className="lg:pl-72">
                <header className="sticky top-0 z-30 border-b border-white/10 bg-slate-950/90 backdrop-blur">
                    <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(true)}
                            className="rounded-xl bg-white/10 p-2 text-white lg:hidden"
                        >
                            <Bars3Icon className="h-6 w-6" />
                        </button>

                        <div>
                            <h2 className="text-base font-semibold text-white sm:text-lg">
                                {title}
                            </h2>
                            <p className="hidden text-xs text-slate-400 sm:block">
                                {subtitle}
                            </p>
                        </div>

                        <div className="flex items-center gap-3">
                            <div className="hidden text-right sm:block">
                                <p className="text-sm font-semibold text-white">{auth?.user?.name}</p>
                                <p className="text-xs text-red-300">Administrator</p>
                            </div>

                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-red-500 text-sm font-bold text-white">
                                <UserCircleIcon className="h-6 w-6" />
                            </div>
                        </div>
                    </div>
                </header>

                <main className="px-4 py-6 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}