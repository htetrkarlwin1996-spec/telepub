import { Head, Link, useForm } from '@inertiajs/react';
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    ArrowRightIcon,
    DocumentTextIcon,
    MusicalNoteIcon,
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

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register" />

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
                                Start your music publishing registration.
                            </h1>

                            <p className="mt-5 text-base leading-8 text-slate-300">
                                Create an account, verify OTP, and your signed agreement PDF will be generated
                                automatically with your registered name and date.
                            </p>

                            <div className="mt-8 rounded-3xl border border-pink-400/20 bg-pink-400/10 p-6">
                                <div className="flex items-center gap-3">
                                    <div className="rounded-2xl bg-white/10 p-3">
                                        <DocumentTextIcon className="h-6 w-6 text-pink-300" />
                                    </div>

                                    <div>
                                        <p className="font-bold text-white">Auto Signed Agreement PDF</p>
                                        <p className="mt-1 text-sm text-slate-400">Generated after OTP verification.</p>
                                    </div>
                                </div>

                                <div className="mt-5 grid gap-3 text-sm text-slate-300">
                                    <p>✓ User wallet and payout requests</p>
                                    <p>✓ Albums with BMI Work ID / IPI Name tracking</p>
                                    <p>✓ KBZ Bank, KBZ Pay, Wave Pay and Thai Bank methods</p>
                                    <p>✓ Take down request management</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex w-full items-center justify-center px-4 py-10 sm:px-6 lg:w-[560px] lg:border-l lg:border-white/10 lg:bg-black/20">
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
                                        <p className="text-sm font-bold text-sky-300">Register</p>
                                        <h2 className="text-2xl font-black text-white">Create account</h2>
                                    </div>
                                </div>

                                <p className="mt-5 text-sm leading-6 text-slate-400">
                                    Your legal name will be used for the signed agreement PDF.
                                </p>

                                <form onSubmit={submit} className="mt-7 space-y-5">
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Legal Name
                                        </label>

                                        <div className="relative">
                                            <UserIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />

                                            <input
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                required
                                                autoFocus
                                                autoComplete="name"
                                                placeholder="Your legal name"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.name && <p className="mt-2 text-sm text-pink-300">{errors.name}</p>}
                                    </div>

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
                                                autoComplete="username"
                                                placeholder="name@example.com"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.email && <p className="mt-2 text-sm text-pink-300">{errors.email}</p>}
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
                                                autoComplete="new-password"
                                                placeholder="••••••••"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.password && <p className="mt-2 text-sm text-pink-300">{errors.password}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-slate-300">
                                            Confirm Password
                                        </label>

                                        <div className="relative">
                                            <LockClosedIcon className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" />

                                            <input
                                                type="password"
                                                value={data.password_confirmation}
                                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                                required
                                                autoComplete="new-password"
                                                placeholder="••••••••"
                                                className="block w-full rounded-2xl border border-white/10 bg-black/30 py-3 pl-12 pr-4 text-white outline-none placeholder:text-slate-600 focus:border-sky-500 focus:ring-sky-500"
                                            />
                                        </div>

                                        {errors.password_confirmation && (
                                            <p className="mt-2 text-sm text-pink-300">{errors.password_confirmation}</p>
                                        )}
                                    </div>

                                    <div className="rounded-2xl border border-sky-400/20 bg-sky-400/10 p-4 text-sm leading-6 text-sky-100">
                                        After registration, OTP verification is required. Once verified,
                                        your signed agreement PDF will be generated automatically.
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 to-sky-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-pink-500/25 hover:from-pink-400 hover:to-sky-400 disabled:opacity-60"
                                    >
                                        {processing ? 'Creating account...' : 'Create Account'}
                                        <ArrowRightIcon className="h-4 w-4" />
                                    </button>
                                </form>

                                <p className="mt-6 text-center text-sm text-slate-400">
                                    Already have an account?{' '}
                                    <Link href={route('login')} className="font-bold text-sky-300 hover:text-sky-200">
                                        Login
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