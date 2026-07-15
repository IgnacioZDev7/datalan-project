import { useEffect, useState } from "react";
import Chart from "react-apexcharts";
import PageMeta from "../../components/common/PageMeta";
import api from "../../services/api";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface KPIs {
  productos: number;
  activos: number;
  existencias_bajo_minimo: number;
  alertas: number;
  movimientos_mes: number;
  carretes: number;
}

interface MovMes {
  mes: string;
  entradas: number;
  salidas: number;
}

interface StockCategoria {
  categoria: string;
  total: number;
}

interface MovReciente {
  id: number;
  codigo: string;
  tipo: string;
  fecha: string;
}

function Tarjeta({ titulo, valor, color }: { titulo: string; valor: number | string; color: string }) {
  return (
    <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800">
      <p className="text-sm text-gray-500 dark:text-gray-400">{titulo}</p>
      <p className={`mt-2 text-3xl font-bold ${color}`}>{valor}</p>
    </div>
  );
}

export default function Panel() {
  const [kpis, setKpis] = useState<KPIs>({
    productos: 0,
    activos: 0,
    existencias_bajo_minimo: 0,
    alertas: 0,
    movimientos_mes: 0,
    carretes: 0,
  });
  const [movimientosMes, setMovimientosMes] = useState<MovMes[]>([]);
  const [stockCategoria, setStockCategoria] = useState<StockCategoria[]>([]);
  const [recientes, setRecientes] = useState<MovReciente[]>([]);

  useEffect(() => {
    api.get("/dashboard/estadisticas").then((r) => {
      setKpis(r.data.kpis);
      setMovimientosMes(r.data.movimientos_por_mes);
      setStockCategoria(r.data.stock_por_categoria);
    });

    api.get("/movimientos", { params: { per_page: 5 } }).then((r) => setRecientes(r.data.data));
  }, []);

  // --- Gráfica de barras: movimientos por mes ---
  const barOptions: ApexCharts.ApexOptions = {
    chart: { type: "bar", toolbar: { show: false } },
    plotOptions: {
      bar: { borderRadius: 4, columnWidth: "50%" },
    },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 1, colors: ["transparent"] },
    xaxis: {
      categories: movimientosMes.map((m) => {
        const [y, mo] = m.mes.split("-");
        const nombres = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        return `${nombres[parseInt(mo)]} ${y.slice(2)}`;
      }),
      labels: { style: { colors: "#9CA3AF", fontSize: "12px" } },
    },
    yaxis: { labels: { style: { colors: "#9CA3AF", fontSize: "12px" } } },
    colors: ["#12B76A", "#F04438"],
    legend: { position: "top", horizontalAlign: "right" },
    tooltip: { y: { formatter: (val: number) => `${val} movimientos` } },
    grid: { borderColor: "#E5E7EB", strokeDashArray: 4 },
  };

  const barSeries = [
    { name: "Entradas", data: movimientosMes.map((m) => m.entradas) },
    { name: "Salidas", data: movimientosMes.map((m) => m.salidas) },
  ];

  // --- Gráfica de dona: stock por categoría ---
  const donutOptions: ApexCharts.ApexOptions = {
    chart: { type: "donut" },
    labels: stockCategoria.map((s) => s.categoria),
    colors: ["#0047AB", "#4DA6FF", "#0A2A66", "#7FB3FF", "#102A43", "#3D8BFF", "#007BFF"],
    legend: { position: "bottom", fontSize: "12px" },
    tooltip: { y: { formatter: (val: number) => `${val} unidades` } },
    plotOptions: {
      pie: {
        donut: {
          size: "65%",
          labels: {
            show: true,
            name: { show: true, fontSize: "14px" },
            value: { show: true, fontSize: "20px", fontWeight: 600 },
            total: {
              show: true,
              label: "Total",
              formatter: () =>
                stockCategoria.reduce((acc, s) => acc + s.total, 0).toLocaleString(),
            },
          },
        },
      },
    },
  };

  const donutSeries = stockCategoria.map((s) => s.total);

  return (
    <>
      <PageMeta title="Panel | DATALAN" description="Panel de inventario DATALAN" />

      <h1 className="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Panel de inventario</h1>

      {/* --- KPIs --- */}
      <div className="grid grid-cols-2 gap-4 mb-6 lg:grid-cols-3 xl:grid-cols-6">
        <Tarjeta titulo="Productos" valor={kpis.productos} color="text-brand-600" />
        <Tarjeta titulo="Activos" valor={kpis.activos} color="text-gray-800 dark:text-white/90" />
        <Tarjeta titulo="Bajo mínimo" valor={kpis.existencias_bajo_minimo} color="text-error-500" />
        <Tarjeta titulo="Alertas" valor={kpis.alertas} color="text-warning-500" />
        <Tarjeta titulo="Movimientos (mes)" valor={kpis.movimientos_mes} color="text-blue-600" />
        <Tarjeta titulo="Carretes" valor={kpis.carretes} color="text-brand-500" />
      </div>

      {/* --- Gráficas --- */}
      {(movimientosMes.length > 0 || stockCategoria.length > 0) && (
        <div className="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
          {movimientosMes.length > 0 && (
            <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800">
              <h2 className="mb-4 font-medium text-gray-800 dark:text-white/90">Movimientos por mes</h2>
              <Chart options={barOptions} series={barSeries} type="bar" height={350} />
            </div>
          )}

          {stockCategoria.length > 0 && (
            <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800">
              <h2 className="mb-4 font-medium text-gray-800 dark:text-white/90">Stock por categoría</h2>
              <Chart options={donutOptions} series={donutSeries} type="donut" height={350} />
            </div>
          )}
        </div>
      )}

      {/* --- Movimientos recientes --- */}
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="px-5 py-3 border-b border-gray-100 dark:border-gray-800">
          <h2 className="font-medium text-gray-800 dark:text-white/90">Movimientos recientes</h2>
        </div>
        <Table>
          <TableHeader className="border-b border-gray-100 dark:border-gray-800">
            <TableRow>
              {["Código", "Tipo", "Fecha"].map((h) => (
                <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>
              ))}
            </TableRow>
          </TableHeader>
          <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
            {recientes.length === 0 ? (
              <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin movimientos.</TableCell></TableRow>
            ) : (
              recientes.map((m) => (
                <TableRow key={m.id}>
                  <TableCell className="px-5 py-3 text-gray-700 text-theme-sm dark:text-gray-300">{m.codigo}</TableCell>
                  <TableCell className="px-5 py-3 text-gray-500 capitalize text-theme-sm dark:text-gray-400">{m.tipo}</TableCell>
                  <TableCell className="px-5 py-3 text-gray-500 text-theme-sm dark:text-gray-400">{String(m.fecha).slice(0, 10)}</TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>
    </>
  );
}
