import React, { useEffect, useState } from "react";
import { Head, Link, router, usePage, useForm } from "@inertiajs/react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import {
    IconClockHour6,
    IconSearch,
    IconCalendar,
    IconAlertCircle,
    IconPlus,
} from "@tabler/icons-react";
import toast from "react-hot-toast";

const formatCurrency = (value = 0) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);

const formatDate = (value) => {
    if (!value) return "-";
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

export default function PayablesIndex({ payables, filters = {}, suppliers = [] }) {
    const { flash } = usePage().props;
    const [search, setSearch] = useState(filters.invoice || "");
    const [status, setStatus] = useState(filters.status || "");
    const [supplierId, setSupplierId] = useState(filters.supplier || "");
    const { data, setData, post, processing, reset, errors } = useForm({
        supplier_id: "",
        document_number: "",
        total: "",
        due_date: "",
        note: "",
    });

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash]);

    const applyFilter = (e) => {
        e.preventDefault();
        router.get(
            route("payables.index"),
            { invoice: search, status, supplier: supplierId },
            { preserveScroll: true, preserveState: true }
        );
    };

    const statusBadge = (value) => {
        const base = "px-2 py-1 text-xs font-semibold rounded-full";
        switch (value) {
            case "paid":
                return <span className={`${base} bg-success-100 text-success-700`}>Lunas</span>;
            case "partial":
                return <span className={`${base} bg-primary-100 text-primary-700`}>Parsial</span>;
            case "overdue":
                return <span className={`${base} bg-rose-100 text-rose-700`}>Jatuh Tempo</span>;
            default:
                return <span className={`${base} bg-amber-100 text-amber-700`}>Belum Lunas</span>;
        }
    };

    const submitCreate = (e) => {
        e.preventDefault();
        post(route("payables.store"), {
            onSuccess: () => reset(),
        });
    };

    const rows = payables?.data || [];

    return (
        <>
            <Head title="Hutang Supplier" />
            <div className="space-y-6">
                <div className="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <IconClockHour6 size={26} className="text-primary-500" />
                            Hutang Supplier
                        </h1>
                        <p className="text-sm text-slate-500">
                            Catat dan lacak pembayaran hutang ke supplier.
                        </p>
                    </div>
                </div>

                {/* Create form */}
                <form
                    onSubmit={submitCreate}
                    className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3"
                >
                    <div>
                        <label className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Supplier
                        </label>
                        <select
                            value={data.supplier_id}
                            onChange={(e) => setData("supplier_id", e.target.value)}
                            className="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                        >
                            <option value="">Umum</option>
                            {suppliers.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Nomor Dokumen
                        </label>
                        <input
                            value={data.document_number}
                            onChange={(e) => setData("document_number", e.target.value)}
                            className="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                            placeholder="Opsional"
                        />
                    </div>
                    <div>
                        <label className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Total
                        </label>
                        <input
                            type="number"
                            min="1"
                            value={data.total}
                            onChange={(e) => setData("total", e.target.value)}
                            className="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                            required
                        />
                        {errors.total && <p className="text-xs text-danger-500">{errors.total}</p>}
                    </div>
                    <div>
                        <label className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Jatuh Tempo
                        </label>
                        <input
                            type="date"
                            value={data.due_date}
                            onChange={(e) => setData("due_date", e.target.value)}
                            className="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                        />
                    </div>
                    <div className="flex items-end">
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full h-11 rounded-xl bg-primary-500 text-white text-sm font-semibold flex items-center justify-center gap-2"
                        >
                            <IconPlus size={16} />
                            Simpan
                        </button>
                    </div>
                    <div className="md:col-span-5">
                        <label className="text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Catatan
                        </label>
                        <textarea
                            rows={2}
                            value={data.note}
                            onChange={(e) => setData("note", e.target.value)}
                            className="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                            placeholder="Catatan tambahan (opsional)"
                        />
                    </div>
                </form>

                {/* Filters */}
                <form
                    onSubmit={applyFilter}
                    className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 flex flex-wrap gap-3 items-center"
                >
                    <div className="relative flex-1 min-w-[200px]">
                        <IconSearch
                            size={18}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari nomor dokumen"
                            className="w-full h-11 pl-10 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                        />
                    </div>
                    <div>
                        <select
                            value={supplierId}
                            onChange={(e) => setSupplierId(e.target.value)}
                            className="h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                        >
                            <option value="">Semua Supplier</option>
                            {suppliers.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="relative">
                        <IconCalendar
                            size={18}
                            className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                        />
                        <select
                            value={status}
                            onChange={(e) => setStatus(e.target.value)}
                            className="h-11 pl-10 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm"
                        >
                            <option value="">Semua Status</option>
                            <option value="unpaid">Belum Lunas</option>
                            <option value="partial">Parsial</option>
                            <option value="paid">Lunas</option>
                            <option value="overdue">Jatuh Tempo</option>
                        </select>
                    </div>
                    <button
                        type="submit"
                        className="px-4 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold"
                    >
                        Terapkan
                    </button>
                </form>

                {/* Table */}
                <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                    <div className="w-full overflow-x-auto">
                        <div className="min-w-[720px]">
                            <div className="grid grid-cols-12 px-3 sm:px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                                <div className="col-span-2">Dokumen</div>
                                <div className="col-span-3">Supplier</div>
                                <div className="col-span-2 text-right">Total</div>
                                <div className="col-span-2 text-right">Sisa</div>
                                <div className="col-span-2 text-right">Jatuh Tempo</div>
                                <div className="col-span-1 text-center">Status</div>
                            </div>
                            {rows.length ? (
                                rows.map((item) => (
                                    <Link
                                        key={item.id}
                                        href={route("payables.show", item.id)}
                                        className="grid grid-cols-12 gap-2 px-3 sm:px-4 py-3 items-center border-b border-slate-100 dark:border-slate-800 hover:bg-primary-50/50 dark:hover:bg-slate-800/50 transition-colors"
                                    >
                                        <div className="col-span-2">
                                            <p className="text-sm font-semibold text-slate-800 dark:text-white">
                                                {item.document_number}
                                            </p>
                                        </div>
                                        <div className="col-span-3">
                                            <p className="text-sm text-slate-700 dark:text-slate-200">
                                                {item.supplier?.name || "-"}
                                            </p>
                                        </div>
                                        <div className="col-span-2 text-right text-sm font-semibold text-slate-900 dark:text-white">
                                            {formatCurrency(item.total)}
                                        </div>
                                        <div className="col-span-2 text-right text-sm font-semibold text-primary-600 dark:text-primary-400">
                                            {formatCurrency(item.remaining)}
                                        </div>
                                        <div className="col-span-2 text-right text-sm text-slate-600 dark:text-slate-400">
                                            {formatDate(item.due_date)}
                                        </div>
                                        <div className="col-span-1 flex justify-center">
                                            {statusBadge(item.status)}
                                        </div>
                                    </Link>
                                ))
                            ) : (
                                <div className="p-8 text-center text-slate-500">
                                    <IconAlertCircle
                                        size={28}
                                        className="mx-auto mb-2 text-slate-400"
                                    />
                                    Belum ada data hutang.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

PayablesIndex.layout = (page) => <DashboardLayout children={page} />;
