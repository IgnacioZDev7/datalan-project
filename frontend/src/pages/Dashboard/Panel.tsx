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

interface ActivoSituacion {
  situacion: string;
  total: number;
}

interface MovReciente {
  id: number;
  codigo: string;
  tipo: string;
  fecha: string;
}

const SITUACION_LABEL: Record<string, string> = {
  en_almacen: "En almacén",
  asignado: "Asignado",
  instalado: "Instalado",
  de_baja: "De baja",
};

const fmt = (n: number) =>
  Number(n).toLocaleString("es-BO", { maximumFractionDigits: 2 });

// --- Tarjeta KPI con ícono y color de acento ---
function KpiCard({
  titulo,
  valor,
  icono,
  acento,
}: {
  titulo: string;
  valor: number;
  icono: React.ReactNode;
  acento: string;
}) {
  return (
    <div className="flex items-center gap-4 p-5 transition-shadow bg-white border border-gray-200 rounded-2xl hover:shadow-md dark:bg-white/[0.03] dark:border-gray-800">
      <div className={`flex items-center justify-center w-12 h-12 rounded-xl shrink-0 ${acento}`}>
        {icono}
      </div>
      <div className="min-w-0">
        <p className="text-sm text-gray-500 truncate dark:text-gray-400">{titulo}</p>
        <p className="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{fmt(valor)}</p>
      </div>
    </div>
  );
}

// Íconos inline (sin dependencias externas)
const Ico = {
  productos: (
    <svg className="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
    </svg>
  ),
  activos: (
    <svg className="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
    </svg>
  ),
  alertaMin: (
    <svg className="w-6 h-6 text-error-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
    </svg>
  ),
  campana: (
    <svg className="w-6 h-6 text-warning-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
    </svg>
  ),
  movimientos: (
    <svg className="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
    </svg>
  ),
  carretes: (
    <svg className="w-6 h-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0-5a4 4 0 100-8 4 4 0 000 8zm0-3a1 1 0 100-2 1 1 0 000 2z" />
    </svg>
  ),
};

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
  const [activosSituacion, setActivosSituacion] = useState<ActivoSituacion[]>([]);
  const [recientes, setRecientes] = useState<MovReciente[]>([]);
  const [cargando, setCargando] = useState(true);

  useEffect(() => {
    setCargando(true);
    Promise.all([
      api.get("/dashboard/estadisticas"),
      api.get("/movimientos", { params: { per_page: 5 } }),
    ])
      .then(([est, movs]) => {
        setKpis(est.data.kpis);
        setMovimientosMes(est.data.movimientos_por_mes ?? []);
        // Number(): SUM() puede venir como string desde el motor de BD
        setStockCategoria(
          (est.data.stock_por_categoria ?? []).map((s: StockCategoria) => ({
            categoria: s.categoria,
            total: Number(s.total),
          }))
        );
        setActivosSituacion(
          (est.data.activos_por_situacion ?? []).map((a: ActivoSituacion) => ({
            situacion: a.situacion,
            total: Number(a.total),
          }))
        );
        setRecientes(movs.data.data ?? []);
      })
      .finally(() => setCargando(false));
  }, []);

  // Top 7 categorías + "Otros" para no saturar la dona
  const stockAgrupado = (() => {
    const orden = [...stockCategoria].sort((a, b) => b.total - a.total);
    if (orden.length <= 8) return orden;
    const top = orden.slice(0, 7);
    const otros = orden.slice(7).reduce((acc, s) => acc + s.total, 0);
    return [...top, { categoria: "Otros", total: otros }];
  })();

  const totalStock = stockCategoria.reduce((acc, s) => acc + s.total, 0);

  // --- Gráfica de barras: movimientos por mes ---
  const barOptions: ApexCharts.ApexOptions = {
    chart: { type: "bar", toolbar: { show: false }, fontFamily: "inherit" },
    plotOptions: { bar: { borderRadius: 4, columnWidth: "45%" } },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ["transparent"] },
    xaxis: {
      categories: movimientosMes.map((m) => {
        const [y, mo] = m.mes.split("-");
        const nombres = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        return `${nombres[parseInt(mo)]} ${y.slice(2)}`;
      }),
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { colors: "#9CA3AF", fontSize: "12px" } },
    },
    yaxis: { labels: { style: { colors: "#9CA3AF", fontSize: "12px" } } },
    colors: ["#12B76A", "#F04438"],
    legend: { position: "top", horizontalAlign: "right", labels: { colors: "#9CA3AF" } },
    tooltip: { y: { formatter: (val: number) => `${val} mov.` } },
    grid: { borderColor: "#E5E7EB", strokeDashArray: 4, xaxis: { lines: { show: false } } },
  };

  const barSeries = [
    { name: "Entradas", data: movimientosMes.map((m) => m.entradas) },
    { name: "Salidas", data: movimientosMes.map((m) => m.salidas) },
  ];

  // --- Gráfica de dona: stock por categoría ---
  const donutOptions: ApexCharts.ApexOptions = {
    chart: { type: "donut", fontFamily: "inherit" },
    labels: stockAgrupado.map((s) => s.categoria),
    colors: ["#0047AB", "#3D8BFF", "#12B76A", "#F79009", "#7A5AF8", "#F04438", "#06AED4", "#98A2B3"],
    stroke: { width: 0 },
    legend: { position: "bottom", fontSize: "12px", labels: { colors: "#9CA3AF" }, markers: { size: 6 } },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (val: number) => `${fmt(val)} unidades` } },
    plotOptions: {
      pie: {
        donut: {
          size: "68%",
          labels: {
            show: true,
            name: { show: true, fontSize: "13px", color: "#9CA3AF" },
            value: {
              show: true,
              fontSize: "22px",
              fontWeight: 700,
              color: "#0047AB",
              formatter: (val: string) => fmt(Number(val)),
            },
            total: {
              show: true,
              label: "Total",
              color: "#9CA3AF",
              formatter: () => fmt(totalStock),
            },
          },
        },
      },
    },
    responsive: [{ breakpoint: 480, options: { legend: { position: "bottom" } } }],
  };

  const donutSeries = stockAgrupado.map((s) => s.total);

  // --- Gráfica de barras horizontales: activos por situación ---
  const activosBarOptions: ApexCharts.ApexOptions = {
    chart: { type: "bar", toolbar: { show: false }, fontFamily: "inherit" },
    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: "55%", distributed: true } },
    dataLabels: { enabled: true, style: { colors: ["#fff"], fontSize: "12px" } },
    xaxis: {
      categories: activosSituacion.map((a) => SITUACION_LABEL[a.situacion] ?? a.situacion),
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { colors: "#9CA3AF", fontSize: "12px" } },
    },
    yaxis: { labels: { style: { colors: "#9CA3AF", fontSize: "12px" } } },
    colors: ["#3D8BFF", "#12B76A", "#F79009", "#F04438"],
    legend: { show: false },
    tooltip: { y: { formatter: (val: number) => `${val} activos` } },
    grid: { borderColor: "#E5E7EB", strokeDashArray: 4, yaxis: { lines: { show: false } } },
  };

  const activosBarSeries = [{ name: "Activos", data: activosSituacion.map((a) => a.total) }];

  const kpiCards = [
    { titulo: "Productos", valor: kpis.productos, icono: Ico.productos, acento: "bg-brand-50 dark:bg-brand-500/15" },
    { titulo: "Activos", valor: kpis.activos, icono: Ico.activos, acento: "bg-indigo-50 dark:bg-indigo-500/15" },
    { titulo: "Bajo mínimo", valor: kpis.existencias_bajo_minimo, icono: Ico.alertaMin, acento: "bg-error-50 dark:bg-error-500/15" },
    { titulo: "Alertas", valor: kpis.alertas, icono: Ico.campana, acento: "bg-warning-50 dark:bg-warning-500/15" },
    { titulo: "Movimientos (mes)", valor: kpis.movimientos_mes, icono: Ico.movimientos, acento: "bg-blue-50 dark:bg-blue-500/15" },
    { titulo: "Carretes", valor: kpis.carretes, icono: Ico.carretes, acento: "bg-brand-50 dark:bg-brand-500/15" },
  ];

  return (
    <>
      <PageMeta title="Panel | DATALAN" description="Panel de inventario DATALAN" />

      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800 dark:text-white/90">Panel de inventario</h1>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Resumen general del estado del inventario técnico de DATALAN.
        </p>
      </div>

      {/* --- KPIs --- */}
      <div className="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        {kpiCards.map((k) => (
          <KpiCard key={k.titulo} {...k} />
        ))}
      </div>

      {/* --- Gráficas --- */}
      <div className="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-3">
        <div className="p-5 bg-white border border-gray-200 lg:col-span-2 rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
          <h2 className="mb-1 font-semibold text-gray-800 dark:text-white/90">Movimientos por mes</h2>
          <p className="mb-4 text-sm text-gray-500 dark:text-gray-400">Entradas vs. salidas — últimos 6 meses</p>
          {cargando ? (
            <div className="h-[320px] flex items-center justify-center text-gray-400">Cargando…</div>
          ) : movimientosMes.length === 0 ? (
            <div className="h-[320px] flex items-center justify-center text-gray-400">Aún no hay movimientos registrados.</div>
          ) : (
            <Chart options={barOptions} series={barSeries} type="bar" height={320} />
          )}
        </div>

        <div className="p-5 bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
          <h2 className="mb-1 font-semibold text-gray-800 dark:text-white/90">Stock por categoría</h2>
          <p className="mb-4 text-sm text-gray-500 dark:text-gray-400">Distribución de existencias</p>
          {cargando ? (
            <div className="h-[320px] flex items-center justify-center text-gray-400">Cargando…</div>
          ) : stockAgrupado.length === 0 ? (
            <div className="h-[320px] flex items-center justify-center text-gray-400">Sin existencias registradas.</div>
          ) : (
            <Chart options={donutOptions} series={donutSeries} type="donut" height={320} />
          )}
        </div>
      </div>

      {/* --- Fila inferior: activos por situación + movimientos recientes --- */}
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
      {/* Activos por situación */}
      <div className="p-5 bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
        <h2 className="mb-1 font-semibold text-gray-800 dark:text-white/90">Activos por situación</h2>
        <p className="mb-4 text-sm text-gray-500 dark:text-gray-400">Estado del ciclo de vida</p>
        {cargando ? (
          <div className="h-[300px] flex items-center justify-center text-gray-400">Cargando…</div>
        ) : activosSituacion.length === 0 ? (
          <div className="h-[300px] flex items-center justify-center text-gray-400">Sin activos registrados.</div>
        ) : (
          <Chart options={activosBarOptions} series={activosBarSeries} type="bar" height={300} />
        )}
      </div>

      {/* Movimientos recientes */}
      <div className="overflow-hidden bg-white border border-gray-200 lg:col-span-2 rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
        <div className="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
          <h2 className="font-semibold text-gray-800 dark:text-white/90">Movimientos recientes</h2>
        </div>
        <div className="max-w-full overflow-x-auto">
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
                    <TableCell className="px-5 py-3 font-medium text-gray-700 text-theme-sm dark:text-gray-300">{m.codigo}</TableCell>
                    <TableCell className="px-5 py-3 text-theme-sm">
                      <span className="capitalize inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300">
                        {m.tipo}
                      </span>
                    </TableCell>
                    <TableCell className="px-5 py-3 text-gray-500 text-theme-sm dark:text-gray-400">{String(m.fecha).slice(0, 10)}</TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>
      </div>
    </>
  );
}
