import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useAuth } from "../../context/AuthContext";
import Button from "../../components/ui/button/Button";
import { downloadReport } from "../../utils/downloadReport";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface Existencia {
  id: number;
  producto_id: number;
  almacen_id: number;
  stock_actual: number;
  stock_minimo: number;
  stock_maximo: number;
  producto?: { nombre: string; codigo: string };
  almacen?: { nombre: string };
}

export default function Existencias() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina } =
    useCrud<Existencia>("/existencias");

  return (
    <>
      <PageMeta title="Existencias | DATALAN" description="Stock de productos por almacén" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Existencias</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar producto o almacén..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("reportes.exportar") && <>
            <Button size="sm" variant="outline" onClick={() => downloadReport("/reportes/existencias/excel", "existencias.xlsx", { almacen_id: filtros.almacen_id as string })}>Excel</Button>
            <Button size="sm" variant="outline" onClick={() => downloadReport("/reportes/existencias/pdf", "existencias.pdf", { almacen_id: filtros.almacen_id as string })}>PDF</Button>
          </>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>{["Producto", "Código", "Almacén", "Stock Actual", "Stock Mínimo", "Stock Máximo"].map((h) => (
                <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>
              ))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>
              ) : items.length === 0 ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>
              ) : (
                items.map((e) => (
                  <TableRow key={e.id}>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.producto?.nombre ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.producto?.codigo ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.almacen?.nombre ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.stock_actual}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.stock_minimo}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.stock_maximo}</TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
          <span>{meta.total} resultados — página {meta.current_page} de {meta.last_page}</span>
          <div className="flex gap-2">
            <button disabled={meta.current_page <= 1} onClick={() => irAPagina(meta.current_page - 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Anterior</button>
            <button disabled={meta.current_page >= meta.last_page} onClick={() => irAPagina(meta.current_page + 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Siguiente</button>
          </div>
        </div>
      )}
    </>
  );
}
