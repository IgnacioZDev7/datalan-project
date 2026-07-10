import { useEffect, useState } from "react";
import PageMeta from "../../components/common/PageMeta";
import api from "../../services/api";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

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
  const [kpis, setKpis] = useState({ productos: 0, bajoMinimo: 0, activos: 0, alertas: 0 });
  const [recientes, setRecientes] = useState<MovReciente[]>([]);

  useEffect(() => {
    const total = (url: string, params = {}) =>
      api.get(url, { params: { per_page: 1, ...params } }).then((r) => r.data.meta?.total ?? 0);

    Promise.all([
      total("/productos"),
      total("/existencias", { bajo_minimo: 1 }),
      total("/activos"),
      total("/alertas"),
    ]).then(([productos, bajoMinimo, activos, alertas]) =>
      setKpis({ productos, bajoMinimo, activos, alertas })
    );

    api.get("/movimientos", { params: { per_page: 5 } }).then((r) => setRecientes(r.data.data));
  }, []);

  return (
    <>
      <PageMeta title="Panel | DATALAN" description="Panel de inventario DATALAN" />

      <h1 className="mb-5 text-xl font-semibold text-gray-800 dark:text-white/90">Panel de inventario</h1>

      <div className="grid grid-cols-2 gap-4 mb-6 lg:grid-cols-4">
        <Tarjeta titulo="Productos" valor={kpis.productos} color="text-brand-600" />
        <Tarjeta titulo="Bajo mínimo" valor={kpis.bajoMinimo} color="text-error-500" />
        <Tarjeta titulo="Activos" valor={kpis.activos} color="text-gray-800 dark:text-white/90" />
        <Tarjeta titulo="Alertas" valor={kpis.alertas} color="text-warning-500" />
      </div>

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
