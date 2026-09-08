import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, useForm } from '@inertiajs/react';

export default function VerifyOtp({ email, status }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('verification.verify'));
    };

    const resend = (e) => {
        e.preventDefault();
        post(route('verification.send'), {
            preserveScroll: true,
        });
    };

    return (
        <GuestLayout>
            <Head title="Verify OTP" />

            <div className="mb-6 text-center">
                <h1 className="text-2xl font-bold text-gray-900">
                    Verify your email
                </h1>
                <p className="mt-2 text-sm text-gray-600">
                    We sent a 6-digit OTP code to
                </p>
                <p className="mt-1 text-sm font-semibold text-gray-900">
                    {email}
                </p>
            </div>

            {status && (
                <div className="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <label className="mb-2 block text-sm font-medium text-gray-700">
                        OTP Code
                    </label>

                    <input
                        type="text"
                        inputMode="numeric"
                        maxLength="6"
                        value={data.code}
                        onChange={(e) => setData('code', e.target.value.replace(/\D/g, ''))}
                        className="block w-full rounded-xl border-gray-300 text-center text-2xl font-bold tracking-[0.5em] shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="000000"
                    />

                    <InputError message={errors.code} className="mt-2" />
                </div>

                <div className="mt-6">
                    <PrimaryButton className="w-full justify-center" disabled={processing}>
                        Verify OTP
                    </PrimaryButton>
                </div>
            </form>

            <form onSubmit={resend} className="mt-4 text-center">
                <button
                    type="submit"
                    disabled={processing}
                    className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                >
                    Resend OTP Code
                </button>
            </form>
        </GuestLayout>
    );
}