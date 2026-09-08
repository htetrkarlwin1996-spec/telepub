import { Head, Link, useForm } from '@inertiajs/react';
import {
    EnvelopeIcon,
    LockClosedIcon,
    ArrowRightIcon,
    KeyIcon,
} from '@heroicons/react/24/outline';

const logoPath = '/assets/images/logo.png';

function Brand() {
    return (
        <Link href="/" className="inline-flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white p-1.5 shadow-lg shadow-pink-500/20">
                <img src={logoPath} alt="Tele Music Logo" className="h-full w-full object-contain" />
            </div>

            <div>
                <p className="text-xl font-black text-white">Tele Music</p>
                <p className="text-xs text-sky-200">Publishing Administration</p>
            </div>
        </Link>
    );
}

export default function Login({ status }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Login" />

            <div className="min-h-screen overflow-hidden bg-[#020617] text-white">
                <div className="pointer-events-none fixed inset-0">
                    <div className="absolute left-[-12%] top-[-12%] h-80 w-80 rounded-full bg-sky-500/20 blur-3xl" />
                    <div className="absolute right-[-12%] bottom-[-12%] h-96 w-96 rounded-full bg-pink-500/20 blur-3xl" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_35%)]" />
                </div>

                <div className="relative z-10 flex min-h-screen">
                    <div className="hidden flex-1 items-center justify-center p-10 lg:flex">
                        <div className="max-w-xl">
                            <Brand />

                            <div className="mt-12 flex h-28 w-28 items-center justify-center overflow-hidden rounded-[2rem] bg-white p-3 shadow-2xl shadow-sky-500/20">
                                <img src={logoPath} alt="Tele Music Logo" className="h-full w-full object-contain" />
                            </div>

                            <h1 className="mt-10 text-5xl font-black leading-tight">
                                Welcome back to your publishing dashboard.
                            </h1>

                            <p className="mt-5 text-base leading-8 text-slate-300">
                                Login to track albums, signed agreement, wallet balance,
                                payout requests and take down requests.
                            </p>

                            <div className="mt-8 grid gap-4">
                                <div className="rounded-3xl border border-sky-400/20 bg-sky-400/10 p-5">
                                    <p className="font-bold text-white">Secure OTP verified account</p>
                                    <p className="mt-2 text-sm text-slate-400">
                                        Your account is protected with email verification flow.
                                    </p>
                                </div>

                                <div className="rounded-3xl border border-pink-400/20 bg-pink-400/10 p-5">
                                    <p className="font-bold text-white">Publishing data managed securely</p>
                                    <p className="mt-2 text-sm text-slate-400">
                                        BMI Work ID and IPI Name are updated by the admin panel.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex w-full items-center justify-center px-4 py-10 sm:px-6 lg:w-[520px] lg:border-l lg:border-white/10 lg:bg-black/20">
                        <div className="w-full max-w-md">
                            <div className="mb-8 lg:hidden">
                                <Brand />
                            </div>

                            <div className="rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 shadow-2xl backdrop-blur sm:p-8">
                                <div className="flex items-center gap-4">
                                    <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-3xl bg-white p-2 shadow-lg shadow-pink-500/20">
                                        <img src={logoPath} alt="Tele Music Logo" className="h-full w-full object-contain" />
                                    </div>

                                    <div>
                                        <p className="text-sm font-bold text-sky-300">Login</p>
                                        <h2 className="text-2xl font-black text-white">Sign in</h2>
                                    </div>
                                </div>

                                <p className="mt-5 text-sm leading-6 text-slate-400">
                                    Enter your email and password to continue.
                                </p>

                                {status && (
                                    <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                                        {status}
                                    </div>
                                )}

                                <form onSubmit={submit} className="mt-7 space-y-5">
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Email Address
                                        </label>

                                        <div className="relative">
                                            <EnvelopeIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />

                                            <input
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                required
                                                autoFocus
                                                autoComplete="username"
                                                placeholder="name@example.com"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.email && (
                                            <p className="mt-2 text-sm text-pink-300">
                                                {errors.email}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Password
                                        </label>

                                        <div className="relative">
                                            <LockClosedIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />

                                            <input
                                                type="password"
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                required
                                                autoComplete="current-password"
                                                placeholder="••••••••"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.password && (
                                            <p className="mt-2 text-sm text-pink-300">
                                                {errors.password}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <label className="flex items-center gap-3">
                                            <input
                                                type="checkbox"
                                                checked={data.remember}
                                                onChange={(e) => setData('remember', e.target.checked)}
                                                className="h-4 w-4 rounded border-white/10 bg-black/40 text-pink-500 focus:ring-pink-500"
                                            />

                                            <span className="text-sm text-slate-400">Remember me</span>
                                        </label>

                                        <Link
                                            href={route('password.request')}
                                            className="inline-flex items-center gap-1.5 text-sm font-bold text-pink-300 hover:text-pink-200"
                                        >
                                            <KeyIcon className="h-4 w-4" />
                                            Forgot Password?
                                        </Link>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 to-sky-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-pink-500/25 hover:from-pink-400 hover:to-sky-400 disabled:opacity-60"
                                    >
                                        {processing ? 'Signing in...' : 'Login'}
                                        <ArrowRightIcon className="h-4 w-4" />
                                    </button>
                                </form>

                                <div className="mt-5">
                                    <Link
                                        href={route('password.request')}
                                        className="flex w-full items-center justify-center gap-2 rounded-2xl border border-sky-400/20 bg-sky-400/10 px-5 py-3 text-sm font-bold text-sky-200 hover:bg-sky-400/20"
                                    >
                                        <KeyIcon className="h-4 w-4" />
                                        Reset your password
                                    </Link>
                                </div>

                                <p className="mt-6 text-center text-sm text-slate-400">
                                    Don&apos;t have an account?{' '}
                                    <Link href={route('register')} className="font-bold text-sky-300 hover:text-sky-200">
                                        Create account
                                    </Link>
                                </p>
                            </div>

                            <p className="mt-6 text-center text-xs text-slate-600">
                                © {new Date().getFullYear()} Tele Music. All rights reserved.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}