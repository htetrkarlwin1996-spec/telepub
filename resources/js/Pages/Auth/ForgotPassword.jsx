import { Head, Link, useForm } from '@inertiajs/react';
import {
    EnvelopeIcon,
    ArrowRightIcon,
    MusicalNoteIcon,
    ArrowLeftIcon,
} from '@heroicons/react/24/outline';

const logoPath = '/assets/images/logo.png';

function Brand() {
    return (
        <Link href="/" className="inline-flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white p-1.5 shadow-lg shadow-pink-500/20">
                <img
                    src={logoPath}
                    alt="Tele Music Logo"
                    className="h-full w-full object-contain"
                />
            </div>

            <div>
                <p className="text-xl font-black text-white">Tele Music</p>
                <p className="text-xs text-sky-200">Publishing Administration</p>
            </div>
        </Link>
    );
}

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <>
            <Head title="Forgot Password" />

            <div className="min-h-screen overflow-hidden bg-[#020617] text-white">
                <div className="pointer-events-none fixed inset-0">
                    <div className="absolute left-[-12%] top-[-12%] h-80 w-80 rounded-full bg-sky-500/20 blur-3xl" />
                    <div className="absolute right-[-12%] bottom-[-12%] h-96 w-96 rounded-full bg-pink-500/20 blur-3xl" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_35%)]" />
                </div>

                <div className="relative z-10 flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
                    <div className="w-full max-w-md">
                        <div className="mb-8 flex justify-center">
                            <Brand />
                        </div>

                        <div className="rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 shadow-2xl backdrop-blur sm:p-8">
                            <div className="flex items-center gap-4">
                                <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-3xl bg-white p-2 shadow-lg shadow-pink-500/20">
                                    <img
                                        src={logoPath}
                                        alt="Tele Music Logo"
                                        className="h-full w-full object-contain"
                                    />
                                </div>

                                <div>
                                    <p className="text-sm font-bold text-sky-300">
                                        Password Recovery
                                    </p>
                                    <h2 className="text-2xl font-black text-white">
                                        Forgot password?
                                    </h2>
                                </div>
                            </div>

                            <p className="mt-5 text-sm leading-6 text-slate-400">
                                Enter your registered email address. We will send you a secure
                                password reset link.
                            </p>

                            {status && (
                                <div className="mt-6 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm leading-6 text-emerald-300">
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

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 to-sky-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-pink-500/25 hover:from-pink-400 hover:to-sky-400 disabled:opacity-60"
                                >
                                    {processing ? 'Sending reset link...' : 'Send Password Reset Link'}
                                    <ArrowRightIcon className="h-4 w-4" />
                                </button>
                            </form>

                            <div className="mt-6 text-center">
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center gap-2 text-sm font-bold text-sky-300 hover:text-sky-200"
                                >
                                    <ArrowLeftIcon className="h-4 w-4" />
                                    Back to login
                                </Link>
                            </div>
                        </div>

                        <p className="mt-6 text-center text-xs text-slate-600">
                            © {new Date().getFullYear()} Tele Music. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}