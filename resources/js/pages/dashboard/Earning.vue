<script setup>
import { h, computed } from "vue";
import { Head, router } from "@inertiajs/vue3"; // Added router for filtering
import { useVueTable, getCoreRowModel, FlexRender } from "@tanstack/vue-table";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

const props = defineProps({
    totalCollected: Number,
    percentageTypes: Array,
    tableData: Array,
    currentFilter: String, // Pass this from controller to show active state
});

// Function to handle the filter change
const handleFilterChange = (event) => {
    router.get(
        "/admin/earning",
        { filter: event.target.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Dynamic Column Definition
const columns = computed(() => {
    const cols = [
        {
            accessorKey: "date",
            header: "Period", // Changed from Date to Period for Weekly/Monthly context
            cell: ({ row }) =>
                h(
                    "span",
                    { class: "font-medium text-white" },
                    row.original.date
                ),
        },
        {
            accessorKey: "total_amount",
            header: "Total Collected",
            cell: ({ row }) =>
                h(
                    "span",
                    { class: "font-bold text-brand-blue" },
                    `₱${parseFloat(row.original.total_amount).toLocaleString()}`
                ),
        },
    ];

    // Add a column for every percentage type (Tax, Bank, etc.)
    props.percentageTypes.forEach((type) => {
        cols.push({
            accessorKey: type.name,
            header: type.name.replace("_", " "),
            cell: ({ row }) => {
                const val = row.original[type.name] || 0;
                return h(
                    "span",
                    { class: "text-brand-gray" },
                    `₱${parseFloat(val).toLocaleString()}`
                );
            },
        });
    });

    return cols;
});

const table = useVueTable({
    get data() {
        return props.tableData;
    },
    get columns() {
        return columns.value;
    },
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Earnings Report" />
    <div class="min-h-screen text-white p-6 bg-brand-dark-black">
        <div class="max-w-7xl mx-auto space-y-6">
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
            >
                <div>
                    <h1 class="text-3xl font-bold text-brand-blue">
                        Earnings Report
                    </h1>
                    <p class="text-brand-gray text-sm">
                        Financial distribution breakdown grouped by period.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex flex-col">
                        <label
                            class="text-[10px] uppercase text-brand-gray font-bold mb-1"
                            >View Mode</label
                        >
                        <select
                            @change="handleFilterChange"
                            :value="currentFilter || 'daily'"
                            class="bg-brand-light-black border border-brand-border-black text-white rounded-lg px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-blue"
                        >
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div
                        class="bg-brand-blue/20 border border-brand-blue/50 p-4 rounded-xl min-w-[200px]"
                    >
                        <p class="text-xs uppercase text-brand-blue font-bold">
                            Total Collected
                        </p>
                        <p class="text-2xl font-black">
                            ₱{{ totalCollected.toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="bg-brand-dark-black rounded-2xl p-6 border border-brand-border-black shadow-2xl"
            >
                <div
                    class="rounded-xl border border-brand-border-black overflow-x-auto bg-brand-light-black/20"
                >
                    <Table>
                        <TableHeader class="bg-brand-light-black">
                            <TableRow
                                v-for="headerGroup in table.getHeaderGroups()"
                                :key="headerGroup.id"
                                class="border-b border-brand-border-black"
                            >
                                <TableHead
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="text-white font-bold uppercase text-[10px] p-4 tracking-wider"
                                >
                                    <FlexRender
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                class="border-b border-brand-border-black hover:bg-brand-blue/5 transition-colors"
                            >
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="p-4"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>

                            <tr v-if="tableData.length === 0">
                                <td
                                    :colspan="percentageTypes.length + 2"
                                    class="p-12 text-center text-brand-gray italic"
                                >
                                    No paid transactions found for this period.
                                </td>
                            </tr>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </div>
</template>
