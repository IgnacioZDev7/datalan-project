import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import Badge from "../../components/ui/badge/Badge";
import { ActivoBadge } from "../../components/common/EstadoBadge";
import { Acciones, BotonAccion, IconoEditar, IconoEliminar } from "../../components/common/TablaAcciones";
import { Modal } from "../../components/ui/modal";
import Button from "../../components/ui/button/Button";
import Input from "../../components/form/input/InputField";
import Label from "../../components/form/Label";

const TIPO_COLOR: Record<string, React.ComponentProps<typeof Badge>["color"]> = {
  info: "info", warning: "warning", error: "error", success: "success", stock_minimo: "warning",
};
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface Alerta {
  id: number;
  tipo: string;
  mensaje: string;
  leida: boolean;
  activo: boolean;
}

const FORM_VACIO = { tipo: "info", mensaje: "", leida: false, activo: true };

export default function Alertas() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Alerta>("/alertas");
  const { isOpen, openModal, closeModal } = useModal();

  const [editando, setEditando] = useState<Alerta | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(e: Alerta) { setEditando(e); setForm({ tipo: e.tipo, mensaje: e.mensaje, leida: e.leida, activo: e.activo }); setErrores({}); openModal(); }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      if (editando) await actualizar(editando.id, form); else await crear(form);
      closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(e: Alerta) { if (!confirm(`¿Eliminar la alerta?`)) return; await eliminar(e.id); }

  return (
    <>
      <PageMeta title="Alertas | DATALAN" description="Gestión de alertas" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Alertas</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("alertas.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nueva</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 bg-gray-50 dark:bg-white/[0.02] dark:border-gray-800">
              <TableRow>{["Tipo", "Mensaje", "Leída", "Estado", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((e) => (<TableRow key={e.id} className="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <TableCell className="px-5 py-4 text-theme-sm"><Badge variant="light" color={TIPO_COLOR[e.tipo] ?? "light"} size="sm">{e.tipo}</Badge></TableCell>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.mensaje}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><Badge variant="light" color={e.leida ? "light" : "primary"} size="sm">{e.leida ? "Leída" : "No leída"}</Badge></TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><ActivoBadge activo={e.activo} /></TableCell>
                <TableCell className="px-5 py-4">
                  <Acciones>
                    {puede("alertas.editar") && <BotonAccion titulo="Editar" color="brand" onClick={() => abrirEditar(e)}>{IconoEditar}</BotonAccion>}
                    {puede("alertas.eliminar") && <BotonAccion titulo="Eliminar" color="error" onClick={() => borrar(e)}>{IconoEliminar}</BotonAccion>}
                  </Acciones>
                </TableCell>
              </TableRow>))}
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
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-lg p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar alerta" : "Nueva alerta"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div><Label>Tipo *</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.tipo} onChange={(e) => setForm({ ...form, tipo: e.target.value })}>
              <option value="info">Info</option>
              <option value="warning">Advertencia</option>
              <option value="error">Error</option>
              <option value="success">Éxito</option>
            </select>
            {errores.tipo && <p className="mt-1 text-xs text-error-500">{errores.tipo[0]}</p>}
          </div>
          <div><Label>Mensaje *</Label><Input value={form.mensaje} onChange={(e) => setForm({ ...form, mensaje: e.target.value })} />{errores.mensaje && <p className="mt-1 text-xs text-error-500">{errores.mensaje[0]}</p>}</div>
          <div className="flex gap-6">
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" checked={form.leida} onChange={(e) => setForm({ ...form, leida: e.target.checked })} /> Leída
            </label>
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" checked={form.activo} onChange={(e) => setForm({ ...form, activo: e.target.checked })} /> Activo
            </label>
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
