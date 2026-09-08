import { Head, useForm, router } from '@inertiajs/react';
import UserDashboardLayout from '@/Layouts/UserDashboardLayout';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';
import { useState } from 'react';
import {
    CreditCardIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    CheckBadgeIcon,
    XMarkIcon,
    StarIcon,
    BuildingLibraryIcon,
    DevicePhoneMobileIcon,
} from '@heroicons/react/24/outline';

const methodTypes = [
    { value: 'kbz_bank', label: 'KBZ Bank' },
    { value: 'thai_bank', label: 'Thai Bank' },
    { value: 'kbz_pay', label: 'KBZ Pay' },
    { value: 'wave_pay', label: 'Wave Pay' },
];

const thaiBanks = [
    'Bangkok Bank',
    'Kasikorn Bank',
    'Siam Commercial Bank',
    'Krungthai Bank',
    'Bank of Ayudhya',
    'TMBThanachart Bank',
    'Government Savings Bank',
    'CIMB Thai Bank',
    'UOB Thailand',
    'Kiatnakin Phatra Bank',
    'Land and Houses Bank',
    'ICBC Thai',
];

function Field({ label, error, children }) {
    return (
        <div>
            <label className="mb-2 block text-sm font-medium text-slate-300">
                {label}
            </label>
            {children}
            {error && <p className="mt-2 text-sm text-red-400">{error}</p>}
        </div>
    );
}

function TextInput({ value, onChange, placeholder = '' }) {
    return (
        <input
            type="text"
            value={value || ''}
            onChange={onChange}
            placeholder={placeholder}
            className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none placeholder:text-slate-600 focus:border-indigo-500 focus:ring-indigo-500"
        />
    );
}

function isBankType(type) {
    return type === 'kbz_bank' || type === 'thai_bank';
}

function isWalletType(type) {
    return type === 'kbz_pay' || type === 'wave_pay';
}

export default function Index({ methods = [], status }) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editingMethod, setEditingMethod] = useState(null);

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm({
        type: 'kbz_pay',
        bank_name: '',
        account_name: '',
        account_number: '',
        phone_number: '',
        bank_branch: '',
        is_default: false,
    });

    const openCreate = () => {
        setEditingMethod(null);
        clearErrors();
        reset();

        setData({
            type: 'kbz_pay',
            bank_name: '',
            account_name: '',
            account_number: '',
            phone_number: '',
            bank_branch: '',
            is_default: methods.length === 0,
        });

        setModalOpen(true);
    };

    const openEdit = (method) => {
        setEditingMethod(method);
        clearErrors();

        setData({
            type: method.type || 'kbz_pay',
            bank_name: method.bank_name || '',
            account_name: method.account_name || '',
            account_number: method.account_number || '',
            phone_number: method.phone_number || '',
            bank_branch: method.bank_branch || '',
            is_default: method.is_default || false,
        });

        setModalOpen(true);
    };

    const closeModal = () => {
        setModalOpen(false);
        setEditingMethod(null);
        clearErrors();
    };

    const handleTypeChange = (type) => {
        setData((previous) => ({
            ...previous,
            type,
            bank_name: type === 'kbz_bank' ? 'KBZ Bank' : type === 'thai_bank' ? previous.bank_name : '',
            account_number: isBankType(type) ? previous.account_number : '',
            phone_number: isWalletType(type) ? previous.phone_number : '',
            bank_branch: isBankType(type) ? previous.bank_branch : '',
        }));
    };

    const submit = (e) => {
        e.preventDefault();

        if (editingMethod) {
            put(route('user.payment-methods.update', editingMethod.id), {
                preserveScroll: true,
                onSuccess: closeModal,
            });
        } else {
            post(route('user.payment-methods.store'), {
                preserveScroll: true,
                onSuccess: closeModal,
            });
        }
    };

    const deleteMethod = (method) => {
        if (!confirm(`Delete ${method.type_label}?`)) {
            return;
        }

        router.delete(route('user.payment-methods.destroy', method.id), {
            preserveScroll: true,
        });
    };

    const setDefault = (method) => {
        router.post(route('user.payment-methods.default', method.id), {}, {
            preserveScroll: true,
        });
    };

    return (
        <UserDashboardLayout
            title="Payment Methods"
            subtitle="Manage KBZ Bank, Thai Bank, KBZ Pay and Wave Pay payout accounts."
        >
            <Head title="Payment Methods" />

            <div className="rounded-3xl border border-white/10 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-black p-6 shadow-2xl sm:p-8">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-400/10 px-3 py-1 text-xs font-semibold text-indigo-300">
                            <CreditCardIcon className="h-4 w-4" />
                            Payout Accounts
                        </div>

                        <h1 className="mt-4 text-2xl font-bold text-white sm:text-4xl">
                            Payment Methods
                        </h1>

                        <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                            Bank methods require account number. KBZ Pay and Wave Pay require phone number only.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={openCreate}
                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                    >
                        <PlusIcon className="h-5 w-5" />
                        Add Method
                    </button>
                </div>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                    {status}
                </div>
            )}

            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                {methods.length > 0 ? (
                    methods.map((method) => (
                        <div
                            key={method.id}
                            className="rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-xl"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex items-center gap-3">
                                    <div className="rounded-2xl bg-white/10 p-3">
                                        {isBankType(method.type) ? (
                                            <BuildingLibraryIcon className="h-6 w-6 text-indigo-300" />
                                        ) : (
                                            <DevicePhoneMobileIcon className="h-6 w-6 text-indigo-300" />
                                        )}
                                    </div>

                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="text-lg font-bold text-white">
                                                {method.type_label}
                                            </h3>

                                            {method.is_default && (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                                                    <CheckBadgeIcon className="h-4 w-4" />
                                                    Default
                                                </span>
                                            )}
                                        </div>

                                        <p className="mt-1 text-sm text-slate-400">
                                            Added on {method.created_at}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-6 grid gap-3 text-sm sm:grid-cols-2">
                                <div className="rounded-2xl bg-black/30 p-4">
                                    <p className="text-xs text-slate-500">Account Name</p>
                                    <p className="mt-1 font-semibold text-white">
                                        {method.account_name}
                                    </p>
                                </div>

                                {isBankType(method.type) && (
                                    <>
                                        <div className="rounded-2xl bg-black/30 p-4">
                                            <p className="text-xs text-slate-500">Bank Name</p>
                                            <p className="mt-1 font-semibold text-white">
                                                {method.bank_name || method.type_label}
                                            </p>
                                        </div>

                                        <div className="rounded-2xl bg-black/30 p-4">
                                            <p className="text-xs text-slate-500">Bank Account Number</p>
                                            <p className="mt-1 font-semibold text-white">
                                                {method.account_number || '-'}
                                            </p>
                                        </div>

                                        <div className="rounded-2xl bg-black/30 p-4">
                                            <p className="text-xs text-slate-500">Bank Branch</p>
                                            <p className="mt-1 font-semibold text-white">
                                                {method.bank_branch || '-'}
                                            </p>
                                        </div>
                                    </>
                                )}

                                {isWalletType(method.type) && (
                                    <div className="rounded-2xl bg-black/30 p-4">
                                        <p className="text-xs text-slate-500">Phone Number</p>
                                        <p className="mt-1 font-semibold text-white">
                                            {method.phone_number || '-'}
                                        </p>
                                    </div>
                                )}
                            </div>

                            <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                                {!method.is_default && (
                                    <button
                                        type="button"
                                        onClick={() => setDefault(method)}
                                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-yellow-400/10 px-4 py-3 text-sm font-semibold text-yellow-300 hover:bg-yellow-400/20"
                                    >
                                        <StarIcon className="h-4 w-4" />
                                        Set Default
                                    </button>
                                )}

                                <button
                                    type="button"
                                    onClick={() => openEdit(method)}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20"
                                >
                                    <PencilSquareIcon className="h-4 w-4" />
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    onClick={() => deleteMethod(method)}
                                    className="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 hover:bg-red-500/20"
                                >
                                    <TrashIcon className="h-4 w-4" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    ))
                ) : (
                    <div className="rounded-3xl border border-dashed border-white/10 bg-white/[0.04] p-10 text-center lg:col-span-2">
                        <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                            <CreditCardIcon className="h-7 w-7 text-indigo-300" />
                        </div>

                        <h3 className="mt-5 text-lg font-bold text-white">
                            No payment methods yet
                        </h3>

                        <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-400">
                            Add KBZ Bank, Thai Bank, KBZ Pay or Wave Pay account to receive payouts.
                        </p>

                        <button
                            type="button"
                            onClick={openCreate}
                            className="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400"
                        >
                            <PlusIcon className="h-5 w-5" />
                            Add First Method
                        </button>
                    </div>
                )}
            </div>

            <Dialog open={modalOpen} onClose={closeModal} className="relative z-50">
                <div className="fixed inset-0 bg-black/80" />

                <div className="fixed inset-0 flex items-center justify-center p-3 sm:p-6">
                    <DialogPanel className="w-full max-w-2xl overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl">
                        <div className="flex items-center justify-between border-b border-white/10 px-5 py-4">
                            <div>
                                <DialogTitle className="text-lg font-bold text-white">
                                    {editingMethod ? 'Edit Payment Method' : 'Add Payment Method'}
                                </DialogTitle>
                                <p className="mt-1 text-xs text-slate-400">
                                    Fields will change based on selected payment type.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={closeModal}
                                className="rounded-xl bg-white/10 p-2 text-white hover:bg-white/20"
                            >
                                <XMarkIcon className="h-5 w-5" />
                            </button>
                        </div>

                        <form onSubmit={submit} className="space-y-5 p-5">
                            <Field label="Payment Type" error={errors.type}>
                                <select
                                    value={data.type}
                                    onChange={(e) => handleTypeChange(e.target.value)}
                                    className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {methodTypes.map((type) => (
                                        <option key={type.value} value={type.value}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            {data.type === 'thai_bank' && (
                                <Field label="Thai Bank Name" error={errors.bank_name}>
                                    <select
                                        value={data.bank_name || ''}
                                        onChange={(e) => setData('bank_name', e.target.value)}
                                        className="block w-full rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-white outline-none focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select Thai Bank</option>
                                        {thaiBanks.map((bank) => (
                                            <option key={bank} value={bank}>
                                                {bank}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            )}

                            {data.type === 'kbz_bank' && (
                                <div className="rounded-2xl border border-indigo-400/20 bg-indigo-400/10 px-4 py-3 text-sm text-indigo-200">
                                    Bank Name: KBZ Bank
                                </div>
                            )}

                            <Field label="Account Name" error={errors.account_name}>
                                <TextInput
                                    value={data.account_name}
                                    onChange={(e) => setData('account_name', e.target.value)}
                                    placeholder="Account holder name"
                                />
                            </Field>

                            {isBankType(data.type) && (
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <Field label="Bank Account Number" error={errors.account_number}>
                                        <TextInput
                                            value={data.account_number}
                                            onChange={(e) => setData('account_number', e.target.value)}
                                            placeholder="Bank account number"
                                        />
                                    </Field>

                                    <Field label="Bank Branch Optional" error={errors.bank_branch}>
                                        <TextInput
                                            value={data.bank_branch}
                                            onChange={(e) => setData('bank_branch', e.target.value)}
                                            placeholder="Branch name optional"
                                        />
                                    </Field>
                                </div>
                            )}

                            {isWalletType(data.type) && (
                                <Field label="Phone Number" error={errors.phone_number}>
                                    <TextInput
                                        value={data.phone_number}
                                        onChange={(e) => setData('phone_number', e.target.value)}
                                        placeholder="09xxxxxxxxx"
                                    />
                                </Field>
                            )}

                            <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/30 px-4 py-3">
                                <input
                                    type="checkbox"
                                    checked={!!data.is_default}
                                    onChange={(e) => setData('is_default', e.target.checked)}
                                    className="rounded border-white/20 bg-black text-indigo-500 focus:ring-indigo-500"
                                />
                                <span className="text-sm text-slate-300">
                                    Set as default payment method
                                </span>
                            </label>

                            <div className="flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-2xl bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-60"
                                >
                                    {processing
                                        ? 'Saving...'
                                        : editingMethod
                                            ? 'Update Method'
                                            : 'Save Method'}
                                </button>
                            </div>
                        </form>
                    </DialogPanel>
                </div>
            </Dialog>
        </UserDashboardLayout>
    );
}