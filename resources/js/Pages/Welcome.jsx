import { Head, Link } from '@inertiajs/react';
import {
    ShieldCheckIcon,
    BanknotesIcon,
    DocumentTextIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    CloudArrowUpIcon,
    MusicalNoteIcon,
} from '@heroicons/react/24/outline';

const logoPath = '/assets/images/logo.png';

function LogoBrand({ compact = false }) {
    return (
        <Link href="/" className="flex items-center gap-3">
            <div className="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-white shadow-lg shadow-pink-500/20">
                <img src={logoPath} alt="Tele Music Logo" className="h-10 w-10 object-contain" />
            </div>

            {!compact && (
                <div>
                    <p className="text-lg font-black tracking-tight text-white">Tele Music</p>
                    <p className="text-xs text-sky-200">Publishing Administration</p>
                </div>
            )}
        </Link>
    );
}

function FeatureCard({ icon: Icon, title, description, tone = 'pink' }) {
    const tones = {
        pink: 'bg-pink-500/15 text-pink-300',
        blue: 'bg-sky-500/15 text-sky-300',
        white: 'bg-white/10 text-white',
    };

    return (
        <div className="rounded-3xl border border-white/10 bg-white/[0.045] p-6 shadow-xl backdrop-blur transition hover:border-pink-400/30 hover:bg-white/[0.075]">
            <div className={`flex h-12 w-12 items-center justify-center rounded-2xl ${tones[tone] || tones.pink}`}>
                <Icon className="h-6 w-6" />
            </div>

            <h3 className="mt-5 text-lg font-bold text-white">{title}</h3>

            <p className="mt-3 text-sm leading-6 text-slate-400">{description}</p>
        </div>
    );
}

function StepItem({ number, title, description }) {
    return (
        <div className="flex gap-4">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-sky-500 text-sm font-bold text-white shadow-lg shadow-pink-500/20">
                {number}
            </div>

            <div>
                <h4 className="font-bold text-white">{title}</h4>
                <p className="mt-1 text-sm leading-6 text-slate-400">{description}</p>
            </div>
        </div>
    );
}

export default function Welcome({ auth, canLogin, canRegister }) {
    return (
        <>
            <Head title="Tele Music Publishing Administration" />

            <div className="min-h-screen overflow-hidden bg-[#020617] text-white">
                <div className="pointer-events-none fixed inset-0">
                    <div className="absolute left-[-12%] top-[-12%] h-80 w-80 rounded-full bg-sky-500/20 blur-3xl" />
                    <div className="absolute right-[-10%] top-[15%] h-96 w-96 rounded-full bg-pink-500/20 blur-3xl" />
                    <div className="absolute bottom-[-15%] left-[25%] h-96 w-96 rounded-full bg-blue-700/20 blur-3xl" />
                    <div className="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_35%)]" />
                </div>

                <header className="relative z-10 border-b border-white/10 bg-black/10 backdrop-blur">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                        <LogoBrand />

                        {canLogin && (
                            <nav className="flex items-center gap-3">
                                {auth?.user ? (
                                    <Link
                                        href={auth.user.role === 'admin' ? route('admin.dashboard') : route('dashboard')}
                                        className="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-950 hover:bg-sky-100"
                                    >
                                        Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={route('login')}
                                            className="hidden rounded-2xl px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10 hover:text-white sm:inline-flex"
                                        >
                                            Login
                                        </Link>

                                        {canRegister && (
                                            <Link
                                                href={route('register')}
                                                className="rounded-2xl bg-gradient-to-r from-pink-500 to-sky-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-pink-500/25 hover:from-pink-400 hover:to-sky-400"
                                            >
                                                Register
                                            </Link>
                                        )}
                                    </>
                                )}
                            </nav>
                        )}
                    </div>
                </header>

                <main className="relative z-10">
                    <section className="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-10 sm:px-6 lg:grid-cols-2 lg:px-8 lg:pb-24 lg:pt-20">
                        <div>
                            <div className="inline-flex items-center gap-2 rounded-full border border-sky-400/20 bg-sky-400/10 px-4 py-2 text-xs font-bold text-sky-200">
                                <ShieldCheckIcon className="h-4 w-4" />
                                Music Publishing Administration System
                            </div>

                            <h1 className="mt-6 text-4xl font-black leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                                Manage your music publishing rights with confidence.
                            </h1>

                            <p className="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                                Register your account, verify with OTP, view signed agreement PDF,
                                track albums, BMI Work ID, IPI Name, payout wallet and take down requests
                                from one secure dashboard.
                            </p>

                            <div className="mt-8 flex flex-col gap-3 sm:flex-row">
                                <Link
                                    href={route('register')}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-pink-500 to-sky-500 px-6 py-4 text-sm font-bold text-white shadow-xl shadow-pink-500/25 hover:from-pink-400 hover:to-sky-400"
                                >
                                    Create Account
                                    <ArrowRightIcon className="h-4 w-4" />
                                </Link>

                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/10 px-6 py-4 text-sm font-bold text-white hover:bg-white/15"
                                >
                                    Login to Dashboard
                                </Link>
                            </div>

                            <div className="mt-8 grid gap-3 sm:grid-cols-3">
                                <div className="rounded-2xl border border-sky-400/20 bg-sky-400/10 p-4">
                                    <p className="text-2xl font-black text-white">OTP</p>
                                    <p className="mt-1 text-xs text-sky-200">Secure Verify</p>
                                </div>

                                <div className="rounded-2xl border border-pink-400/20 bg-pink-400/10 p-4">
                                    <p className="text-2xl font-black text-white">PDF</p>
                                    <p className="mt-1 text-xs text-pink-200">Signed Agreement</p>
                                </div>

                                <div className="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                    <p className="text-2xl font-black text-white">MMK</p>
                                    <p className="mt-1 text-xs text-slate-400">Wallet / Payouts</p>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-sky-500/20 to-pink-500/20 blur-3xl" />

                            <div className="relative rounded-[2rem] border border-white/10 bg-white/[0.06] p-5 shadow-2xl backdrop-blur">
                                <div className="rounded-3xl border border-white/10 bg-slate-950/80 p-5">
                                    <div className="flex items-center justify-between gap-4">
                                        <div className="flex items-center gap-4">
                                            <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-3xl bg-white p-2">
                                                <img src={logoPath} alt="Tele Music Logo" className="h-full w-full object-contain" />
                                            </div>

                                            <div>
                                                <p className="text-sm text-slate-400">Publishing Dashboard</p>
                                                <h2 className="mt-1 text-2xl font-black text-white">Artist Control Panel</h2>
                                            </div>
                                        </div>

                                        <div className="rounded-2xl bg-emerald-400/10 px-3 py-2 text-xs font-bold text-emerald-300">
                                            Active
                                        </div>
                                    </div>

                                    <div className="mt-6 grid gap-3 sm:grid-cols-2">
                                        <div className="rounded-2xl bg-white/[0.05] p-4">
                                            <DocumentTextIcon className="h-6 w-6 text-pink-300" />
                                            <p className="mt-4 text-sm text-slate-400">Agreement</p>
                                            <p className="mt-1 font-bold text-white">Signed PDF Ready</p>
                                        </div>

                                        <div className="rounded-2xl bg-white/[0.05] p-4">
                                            <BanknotesIcon className="h-6 w-6 text-emerald-300" />
                                            <p className="mt-4 text-sm text-slate-400">Wallet</p>
                                            <p className="mt-1 font-bold text-white">Balance + Payout</p>
                                        </div>

                                        <div className="rounded-2xl bg-white/[0.05] p-4">
                                            <MusicalNoteIcon className="h-6 w-6 text-sky-300" />
                                            <p className="mt-4 text-sm text-slate-400">Albums</p>
                                            <p className="mt-1 font-bold text-white">BMI / IPI Tracking</p>
                                        </div>

                                        <div className="rounded-2xl bg-white/[0.05] p-4">
                                            <CloudArrowUpIcon className="h-6 w-6 text-pink-300" />
                                            <p className="mt-4 text-sm text-slate-400">Requests</p>
                                            <p className="mt-1 font-bold text-white">Take Down Links</p>
                                        </div>
                                    </div>

                                    <div className="mt-6 rounded-3xl bg-gradient-to-br from-pink-500/20 to-sky-500/10 p-5">
                                        <div className="flex items-center gap-3">
                                            <CheckCircleIcon className="h-6 w-6 text-emerald-300" />
                                            <p className="font-bold text-white">Album delivery flow supported</p>
                                        </div>

                                        <div className="mt-4 grid gap-2 text-sm text-slate-300">
                                            <p>Pending → BMI Work ID waiting</p>
                                            <p>Completed → BMI Work ID ready</p>
                                            <p>Delivery → IPI Name delivered</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                        <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                            <FeatureCard tone="pink" icon={DocumentTextIcon} title="Signed Agreement PDF" description="After OTP verification, the system automatically generates a signed agreement PDF with the registered name and date." />
                            <FeatureCard tone="blue" icon={MusicalNoteIcon} title="Album Registration" description="Admins can add albums and songs, then update BMI Work ID and IPI Name status for each artist." />
                            <FeatureCard tone="white" icon={BanknotesIcon} title="Wallet & Payouts" description="Users can request payouts from their wallet balance while admins approve, mark paid and send email notifications." />
                            <FeatureCard tone="pink" icon={ShieldCheckIcon} title="Take Down Requests" description="Users can submit song links for take down requests and track admin processing status." />
                        </div>
                    </section>

                    <section className="mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">
                        <div className="grid gap-8 rounded-[2rem] border border-white/10 bg-white/[0.04] p-6 shadow-xl md:grid-cols-2 md:p-8">
                            <div>
                                <p className="text-sm font-bold text-sky-300">How it works</p>
                                <h2 className="mt-3 text-2xl font-black text-white sm:text-3xl">
                                    Simple process for artists and publishers.
                                </h2>
                                <p className="mt-4 text-sm leading-7 text-slate-400">
                                    Tele Music keeps registration, agreement, payout and publishing information in one secure place.
                                </p>
                            </div>

                            <div className="space-y-5">
                                <StepItem number="1" title="Create account and verify OTP" description="User registers, verifies OTP code and gets a signed agreement PDF automatically." />
                                <StepItem number="2" title="Admin adds albums and songs" description="Admin creates albums, adds song list and updates BMI Work ID / IPI Name." />
                                <StepItem number="3" title="User tracks wallet and payout" description="User adds payment methods, requests payout and receives email when paid." />
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="relative z-10 border-t border-white/10 py-8">
                    <div className="mx-auto flex max-w-7xl flex-col gap-3 px-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                        <p>© {new Date().getFullYear()} Tele Music. All rights reserved.</p>
                        <p>Music Publishing Administration System</p>
                    </div>
                </footer>
            </div>
        </>
    );
}