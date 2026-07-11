import { useEffect, useState } from "react";
import PageMeta from "../../components/common/PageMeta";
import { useLookup } from "../../hooks/useLookup";
import { useAuth } from "../../context/AuthContext";
import Button from "../../components/ui/button/Button";
import { downloadReport } from "../../utils/downloadReport";
import api from "../../services/api";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface LineaKardex {
  fecha: string;
  movimiento: string;
  tipo: string;
  entrada: number | null;
  salida: number | null;
  saldo: number;
}
interface Reporte {
  producto: { id: number; codigo: string; nombre: string };
  saldo_final: number;
  movimientos: LineaKardex[];
}

export default function Kardex() {
  const { puede } = useAuth();
  const { items: productos, cargando: cargandoProd } = useLookup("/productos");
  const [productoId, setProductoId] = useState("");
  const [reporte, setReporte] = useState<Reporte | null>(null);
  const [cargando, setCargando] = useState(false);

  useEffect(() => {
    if (!productoId) {
      setReporte(null);
      return;
    }
    setCargando(true);
    api
      .get(`/kardex/producto/${productoId}`)
      .then((r) => setReporte(r.data))
      .finally(() => setCargando(false));
  }, [productoId]);

  return (
    <>
      <PageMeta title="Kardex | DATALAN" description="Kardex de productos" />

      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Kardex</h1>
        <select
          className="h-11 w-full sm:w-96 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
          value={productoId}
          onChange={(e) => setProductoId(e.target.value)}
        >
          <option value="">Selecciona un producto...</option>
          {cargandoProd ? (<option value="" disabled>Cargando...</option>) : productos.map((p) => (
            <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>
          ))}
        </select>
      </div>

      {!productoId ? (
        <p className="text-gray-500">Elige un producto para ver su historial de movimientos.</p>
      ) : cargando ? (
        <p className="text-gray-500">Cargando...</p>
      ) : reporte ? (
        <>
          <div className="flex items-center justify-between p-4 mb-4 border border-gray-200 rounded-xl dark:border-gray-800">
            <div>
              <p className="font-medium text-gray-800 dark:text-white/90">{reporte.producto.codigo} — {reporte.producto.nombre}</p>
              <p className="text-sm text-gray-500">Movimientos registrados: {reporte.movimientos.length}</p>
            </div>
            <div className="text-right">
              <p className="text-sm text-gray-500">Saldo final</p>
              <p className="text-2xl font-bold text-brand-600">{reporte.saldo_final}</p>
              {puede("reportes.exportar") && (
                <Button size="sm" variant="outline" className="mt-2" onClick={() => downloadReport(`/reportes/kardex/${productoId}/pdf`, `kardex-${reporte.producto.codigo}.pdf`)}>Exportar PDF</Button>
              )}
            </div>
          </div>

          <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <div className="max-w-full overflow-x-auto">
              <Table>
                <TableHeader className="border-b border-gray-100 dark:border-gray-800">
                  <TableRow>
                    {["Fecha", "Movimiento", "Tipo", "Entrada", "Salida", "Saldo"].map((h) => (
                      <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>
                    ))}
                  </TableRow>
                </TableHeader>
                <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
                  {reporte.movimientos.length === 0 ? (
                    <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin movimientos.</TableCell></TableRow>
                  ) : (
                    reporte.movimientos.map((l, i) => (
                      <TableRow key={i}>
                        <TableCell className="px-5 py-3 text-gray-500 text-theme-sm dark:text-gray-400">{String(l.fecha).slice(0, 10)}</TableCell>
                        <TableCell className="px-5 py-3 text-gray-700 text-theme-sm dark:text-gray-300">{l.movimiento}</TableCell>
                        <TableCell className="px-5 py-3 text-gray-500 capitalize text-theme-sm dark:text-gray-400">{l.tipo}</TableCell>
                        <TableCell className="px-5 py-3 text-success-600 text-theme-sm">{l.entrada ?? ""}</TableCell>
                        <TableCell className="px-5 py-3 text-error-500 text-theme-sm">{l.salida ?? ""}</TableCell>
                        <TableCell className="px-5 py-3 font-medium text-gray-800 text-theme-sm dark:text-white/90">{l.saldo}</TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
            </div>
          </div>
        </>
      ) : null}
    </>
  );
}
