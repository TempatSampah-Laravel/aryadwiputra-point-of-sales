import React from "react";
import {
    IconTruck,
    IconMapPin,
    IconPhone,
    IconUser,
    IconPackage,
} from "@tabler/icons-react";

/**
 * Shipping Label Component
 * Size: 150x100mm for standard shipping labels
 */
export default function ShippingLabel({ transaction }) {
    const formatPrice = (price = 0) =>
        price.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });

    const formatDate = (value) => {
        if (!value) return "-";
        const d = new Date(value);
        return d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    };

    const handlePrint = () => {
        window.print();
    };

    // Get customer details
    const customer = transaction?.customer || {};
    const hasCustomer = customer?.name;

    return (
        <>
            {/* Print Styles */}
            <style>
                {`
                    @media print {
                        @page {
                            size: 150mm 100mm;
                            margin: 0;
                        }
                        body {
                            margin: 0;
                            padding: 0;
                        }
                        .shipping-label {
                            width: 150mm !important;
                            height: 100mm !important;
                            page-break-after: always;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                `}
            </style>

            {/* Print Button */}
            <button
                onClick={handlePrint}
                className="no-print mb-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors"
            >
                <IconTruck size={18} />
                Cetak Resi
            </button>

            {/* Shipping Label */}
            <div
                className="shipping-label bg-white border-2 border-slate-300 p-4"
                style={{ width: "150mm", height: "100mm" }}
            >
                {/* Header */}
                <div className="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                    <div className="flex items-center gap-2">
                        <IconTruck size={24} className="text-primary-500" />
                        <span className="text-lg font-bold text-slate-800">
                            RESI PENGIRIMAN
                        </span>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-slate-500">No. Invoice</p>
                        <p className="text-sm font-bold text-slate-800">
                            {transaction?.invoice || "-"}
                        </p>
                    </div>
                </div>

                {/* Main Content */}
                <div className="grid grid-cols-2 gap-4">
                    {/* Penerima */}
                    <div className="border border-slate-200 rounded-lg p-3">
                        <div className="flex items-center gap-2 mb-2">
                            <IconUser size={16} className="text-slate-500" />
                            <span className="text-xs font-semibold text-slate-600 uppercase">
                                Penerima
                            </span>
                        </div>
                        {hasCustomer ? (
                            <>
                                <p className="text-lg font-bold text-slate-800">
                                    {customer.name}
                                </p>
                                {customer.phone && (
                                    <p className="text-sm text-slate-600 flex items-center gap-1 mt-1">
                                        <IconPhone size={14} />
                                        {customer.phone}
                                    </p>
                                )}
                                {customer.address && (
                                    <p className="text-sm text-slate-600 flex items-start gap-1 mt-1">
                                        <IconMapPin
                                            size={14}
                                            className="mt-0.5 flex-shrink-0"
                                        />
                                        <span>{customer.address}</span>
                                    </p>
                                )}
                            </>
                        ) : (
                            <p className="text-sm text-slate-400 italic">
                                Pelanggan umum
                            </p>
                        )}
                    </div>

                    {/* Detail Order */}
                    <div className="border border-slate-200 rounded-lg p-3">
                        <div className="flex items-center gap-2 mb-2">
                            <IconPackage size={16} className="text-slate-500" />
                            <span className="text-xs font-semibold text-slate-600 uppercase">
                                Detail Order
                            </span>
                        </div>
                        <div className="space-y-1.5">
                            <div className="flex justify-between text-sm">
                                <span className="text-slate-500">Tanggal:</span>
                                <span className="font-medium text-slate-700">
                                    {formatDate(transaction?.created_at)}
                                </span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-slate-500">
                                    Jumlah Item:
                                </span>
                                <span className="font-medium text-slate-700">
                                    {transaction?.details?.length || 0} item
                                </span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-slate-500">Total:</span>
                                <span className="font-bold text-primary-600">
                                    {formatPrice(transaction?.grand_total)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Product List */}
                <div className="mt-3 border border-slate-200 rounded-lg p-2">
                    <p className="text-xs font-semibold text-slate-600 mb-1">
                        Produk:
                    </p>
                    <div className="text-xs text-slate-600 line-clamp-2">
                        {transaction?.details
                            ?.map(
                                (item) =>
                                    `${item.product?.title || "Produk"} (${
                                        item.qty
                                    }x)`
                            )
                            .join(", ") || "-"}
                    </div>
                </div>

                {/* Footer */}
                <div className="mt-3 pt-2 border-t border-dashed border-slate-300 flex justify-between items-center">
                    <p className="text-xs text-slate-400">
                        Kasir: {transaction?.cashier?.name || "-"}
                    </p>
                    <p className="text-xs text-slate-400">
                        Dicetak: {new Date().toLocaleDateString("id-ID")}
                    </p>
                </div>
            </div>
        </>
    );
}
