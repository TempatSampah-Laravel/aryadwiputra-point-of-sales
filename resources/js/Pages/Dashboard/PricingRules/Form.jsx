import React from "react";
import { Head, useForm } from "@inertiajs/react";
import Button from "@/Components/Dashboard/Button";
import {
    IconArrowLeft,
    IconChartInfographic,
    IconDeviceFloppy,
} from "@tabler/icons-react";

const targetOptions = [
    { value: "all", label: "Semua Produk" },
    { value: "product", label: "Produk Tertentu" },
    { value: "category", label: "Kategori Tertentu" },
];

const customerScopeOptions = [
    { value: "all", label: "Semua Pelanggan" },
    { value: "walk_in", label: "Tanpa Pelanggan / Umum" },
    { value: "registered", label: "Pelanggan Terdaftar" },
    { value: "member", label: "Member Loyalty" },
];

const discountTypeOptions = [
    { value: "percentage", label: "Persentase (%)" },
    { value: "fixed_amount", label: "Potongan Nominal per Unit" },
    { value: "fixed_price", label: "Harga Final per Unit" },
];

function InputError({ message }) {
    if (!message) return null;

    return <p className="mt-1 text-xs text-rose-500">{message}</p>;
}

export default function Form({
    mode = "create",
    rule = null,
    products = [],
    categories = [],
    tierOptions = [],
}) {
    const isEdit = mode === "edit";
    const { data, setData, post, put, processing, errors } = useForm({
        name: rule?.name ?? "",
        is_active: Boolean(rule?.is_active ?? true),
        priority: String(rule?.priority ?? 100),
        target_type: rule?.target_type ?? "all",
        product_id: rule?.product_id ? String(rule.product_id) : "",
        category_id: rule?.category_id ? String(rule.category_id) : "",
        customer_scope: rule?.customer_scope ?? "all",
        eligible_loyalty_tiers: rule?.eligible_loyalty_tiers ?? [],
        discount_type: rule?.discount_type ?? "percentage",
        discount_value: rule?.discount_value ? String(rule.discount_value) : "",
        starts_at: rule?.starts_at
            ? new Date(rule.starts_at).toISOString().slice(0, 16)
            : "",
        ends_at: rule?.ends_at
            ? new Date(rule.ends_at).toISOString().slice(0, 16)
            : "",
        notes: rule?.notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEdit) {
            put(route("pricing-rules.update", rule.id));
            return;
        }

        post(route("pricing-rules.store"));
    };

    return (
        <>
            <Head title={isEdit ? "Edit Promo Harga" : "Buat Promo Harga"} />

            <div className="w-full">
                <div className="mb-6">
                    <Button
                        type="link"
                        href={route("pricing-rules.index")}
                        icon={<IconArrowLeft size={18} />}
                        className="mb-3 border-none bg-transparent px-0 text-slate-500 shadow-none hover:bg-transparent hover:text-primary-600 dark:text-slate-400"
                        label="Kembali ke promo harga"
                    />
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-white">
                        {isEdit ? "Edit Promo Harga" : "Buat Promo Harga"}
                    </h1>
                    <p className="text-sm text-slate-500 dark:text-slate-400">
                        Kelola rule harga otomatis untuk POS.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <div className="mb-4 flex items-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-100 text-primary-600 dark:bg-primary-950/40 dark:text-primary-300">
                                <IconChartInfographic size={22} />
                            </div>
                            <div>
                                <h2 className="text-lg font-semibold text-slate-900 dark:text-white">
                                    Informasi Rule
                                </h2>
                                <p className="text-sm text-slate-500 dark:text-slate-400">
                                    Identitas dasar rule dan prioritas penerapan.
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Nama Rule
                                </label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(event) =>
                                        setData("name", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="Contoh: Promo Kopi Pagi"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Priority
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    value={data.priority}
                                    onChange={(event) =>
                                        setData("priority", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                                <InputError message={errors.priority} />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                            Target & Scope
                        </h2>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Target Rule
                                </label>
                                <select
                                    value={data.target_type}
                                    onChange={(event) =>
                                        setData("target_type", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {targetOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.target_type} />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Scope Pelanggan
                                </label>
                                <select
                                    value={data.customer_scope}
                                    onChange={(event) =>
                                        setData("customer_scope", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {customerScopeOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.customer_scope} />
                            </div>

                            {data.target_type === "product" && (
                                <div className="md:col-span-2">
                                    <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Produk
                                    </label>
                                    <select
                                        value={data.product_id}
                                        onChange={(event) =>
                                            setData("product_id", event.target.value)
                                        }
                                        className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="">Pilih produk</option>
                                        {products.map((product) => (
                                            <option key={product.id} value={product.id}>
                                                {product.title}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.product_id} />
                                </div>
                            )}

                            {data.target_type === "category" && (
                                <div className="md:col-span-2">
                                    <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Kategori
                                    </label>
                                    <select
                                        value={data.category_id}
                                        onChange={(event) =>
                                            setData("category_id", event.target.value)
                                        }
                                        className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="">Pilih kategori</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.category_id} />
                                </div>
                            )}

                            {data.customer_scope === "member" && (
                                <div className="md:col-span-2">
                                    <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Tier Member yang Berhak
                                    </label>
                                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                        {tierOptions.map((tier) => {
                                            const checked =
                                                data.eligible_loyalty_tiers.includes(
                                                    tier.value
                                                );

                                            return (
                                                <label
                                                    key={tier.value}
                                                    className="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={checked}
                                                        onChange={(event) => {
                                                            const nextValues =
                                                                event.target.checked
                                                                    ? [
                                                                          ...data.eligible_loyalty_tiers,
                                                                          tier.value,
                                                                      ]
                                                                    : data.eligible_loyalty_tiers.filter(
                                                                          (value) =>
                                                                              value !== tier.value
                                                                      );

                                                            setData(
                                                                "eligible_loyalty_tiers",
                                                                nextValues
                                                            );
                                                        }}
                                                        className="h-4 w-4 rounded border-slate-300 text-primary-500"
                                                    />
                                                    <span>{tier.label}</span>
                                                </label>
                                            );
                                        })}
                                    </div>
                                    <p className="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        Kosongkan semua pilihan bila promo berlaku untuk semua member.
                                    </p>
                                    <InputError
                                        message={errors.eligible_loyalty_tiers}
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h2 className="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                            Diskon & Jadwal
                        </h2>

                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Tipe Diskon
                                </label>
                                <select
                                    value={data.discount_type}
                                    onChange={(event) =>
                                        setData("discount_type", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {discountTypeOptions.map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.discount_type} />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Nilai Diskon
                                </label>
                                <input
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    value={data.discount_value}
                                    onChange={(event) =>
                                        setData("discount_value", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder={
                                        data.discount_type === "percentage"
                                            ? "10"
                                            : "5000"
                                    }
                                />
                                <InputError message={errors.discount_value} />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Mulai Berlaku
                                </label>
                                <input
                                    type="datetime-local"
                                    value={data.starts_at}
                                    onChange={(event) =>
                                        setData("starts_at", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                                <InputError message={errors.starts_at} />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Selesai Berlaku
                                </label>
                                <input
                                    type="datetime-local"
                                    value={data.ends_at}
                                    onChange={(event) =>
                                        setData("ends_at", event.target.value)
                                    }
                                    className="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                                <InputError message={errors.ends_at} />
                            </div>

                            <div className="md:col-span-2">
                                <label className="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Catatan
                                </label>
                                <textarea
                                    value={data.notes}
                                    onChange={(event) =>
                                        setData("notes", event.target.value)
                                    }
                                    rows={4}
                                    className="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    placeholder="Opsional"
                                />
                                <InputError message={errors.notes} />
                            </div>

                            <div className="md:col-span-2">
                                <label className="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
                                    <input
                                        type="checkbox"
                                        checked={data.is_active}
                                        onChange={(event) =>
                                            setData("is_active", event.target.checked)
                                        }
                                        className="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                    />
                                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        Rule aktif
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <Button
                            type="link"
                            href={route("pricing-rules.index")}
                            className="border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                            label="Batal"
                        />
                        <Button
                            type="submit"
                            disabled={processing}
                            icon={<IconDeviceFloppy size={18} />}
                            className="bg-primary-500 text-white hover:bg-primary-600 disabled:opacity-60"
                            label={processing ? "Menyimpan..." : "Simpan Rule"}
                        />
                    </div>
                </form>
            </div>
        </>
    );
}
