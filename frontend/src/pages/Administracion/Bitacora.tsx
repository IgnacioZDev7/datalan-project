import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface BitacoraEntry {
  id: number;
  log_name: string;
  description: string;
  event: string | null;
  subject_type: string | null;
  subject_id: number | null;
  properties: { attributes?: Record<string, unknown>; old?: Record<string, unknown> } | null;
  causer: { id: number; nombre: string; correo_electronico: string } | null;
  created_at: string;
}

function subjectLabel(subjectType: string | null): string {
  if (!subjectType) return "-";
  const parts = subjectType.split("\\");
  return parts[parts.length - 1];
}

function eventBadge(event: string | null) {
  const map: Record<string, string> = {
    created: "bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400",
    updated: "bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400",
    deleted: "bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-400",
  };
  const labels: Record<string, string> = {
    created: "Creado",
    updated: "Actualizado",
    deleted: "Eliminado",
  };
  const cls = map[event ?? ""] ?? "bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300";
  return (
    <span className={`inline-block px-2 py-0.5 text-xs font-medium rounded-full ${cls}`}>
      {labels[event ?? ""] ?? event ?? "-"}
    </span>
  );
}

export default function Bitacora() {
  const { items, meta, cargando, filtros, filtrar, irAPagina } =
    useCrud<BitacoraEntry>("/bitacora", { per_page: 20 });

  return (
    <>
      <PageMeta title="Bitácora | DATALAN" description="Historial de auditoría del sistema" />

      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Bitácora</h1>
        <div className="flex flex-wrap gap-3">
          <input
            type="text"
            placeholder="Buscar en descripción..."
            className="h-11 w-full sm:w-56 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""}
            onChange={(e) => filtrar({ buscar: e.target.value })}
          />
          <input
            type="date"
            className="h-11 w-full sm:w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.desde as string) ?? ""}
            onChange={(e) => filtrar({ desde: e.target.value })}
          />
          <input
            type="date"
            className="h-11 w-full sm:w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.hasta as string) ?? ""}
            onChange={(e) => filtrar({ hasta: e.target.value })}
          />
        </div>
      </div>

      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>
                {["Fecha", "Usuario", "Acción", "Entidad", "Detalle"].map((h) => (
                  <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">
                    {h}
                  </TableCell>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (
                <TableRow>
                  <td colSpan={5} className="px-5 py-6 text-center text-gray-500">
                    Cargando...
                  </td>
                </TableRow>
              ) : items.length === 0 ? (
                <TableRow>
                  <td colSpan={5} className="px-5 py-6 text-center text-gray-500">
                    Sin registros.
                  </td>
                </TableRow>
              ) : (
                items.map((e) => (
                  <TableRow key={e.id}>
                    <TableCell className="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400">
                      {new Date(e.created_at).toLocaleString("es-BO", {
                        year: "numeric", month: "2-digit", day: "2-digit",
                        hour: "2-digit", minute: "2-digit",
                      })}
                    </TableCell>
                    <TableCell className="px-5 py-3 text-theme-sm text-gray-700 dark:text-gray-300">
                      {e.causer ? (
                        <span>
                          {e.causer.nombre}
                          <span className="block text-xs text-gray-400">{e.causer.correo_electronico}</span>
                        </span>
                      ) : (
                        <span className="text-gray-400 italic">Sistema</span>
                      )}
                    </TableCell>
                    <TableCell className="px-5 py-3">{eventBadge(e.event)}</TableCell>
                    <TableCell className="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400">
                      {subjectLabel(e.subject_type)}
                      {e.subject_id ? ` #${e.subject_id}` : ""}
                    </TableCell>
                    <TableCell className="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                      {e.description}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>

      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
          <span>{meta.total} registros — página {meta.current_page} de {meta.last_page}</span>
          <div className="flex gap-2">
            <button
              disabled={meta.current_page <= 1}
              onClick={() => irAPagina(meta.current_page - 1)}
              className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700"
            >
              Anterior
            </button>
            <button
              disabled={meta.current_page >= meta.last_page}
              onClick={() => irAPagina(meta.current_page + 1)}
              className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700"
            >
              Siguiente
            </button>
          </div>
        </div>
      )}
    </>
  );
}
