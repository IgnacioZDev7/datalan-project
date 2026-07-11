import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import { Modal } from "../../components/ui/modal";
import Button from "../../components/ui/button/Button";
import Label from "../../components/form/Label";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface Asignacion {
  id: number;
  activo_id: number | null;
  tecnico_id: number | null;
  proyecto_id: number | null;
  fecha_asignacion: string | null;
  fecha_devolucion: string | null;
  activo: boolean;
  activo_rel?: { nombre: string };
  tecnico?: { nombre: string };
  proyecto?: { nombre: string };
}

const FORM_VACIO = { activo_id: "", tecnico_id: "", proyecto_id: "", fecha_asignacion: "", fecha_devolucion: "", activo: true };

export default function Asignaciones() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Asignacion>("/asignaciones");
  const { isOpen, openModal, closeModal } = useModal();

  const { items: activos, cargando: cargandoAct } = useLookup("/activos");
  const { items: tecnicos, cargando: cargandoTec } = useLookup("/tecnicos");
  const { items: proyectos, cargando: cargandoProy } = useLookup("/proyectos");
  const [editando, setEditando] = useState<Asignacion | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(e: Asignacion) {
    setEditando(e);
    setForm({
      activo_id: String(e.activo_id ?? ""),
      tecnico_id: String(e.tecnico_id ?? ""),
      proyecto_id: String(e.proyecto_id ?? ""),
      fecha_asignacion: e.fecha_asignacion ?? "",
      fecha_devolucion: e.fecha_devolucion ?? "",
      activo: e.activo,
    });
    setErrores({}); openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const datos = {
        ...form,
        activo_id: form.activo_id ? Number(form.activo_id) : null,
        tecnico_id: form.tecnico_id ? Number(form.tecnico_id) : null,
        proyecto_id: form.proyecto_id ? Number(form.proyecto_id) : null,
      };
      if (editando) await actualizar(editando.id, datos); else await crear(datos);
      closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(e: Asignacion) { if (!confirm(`¿Eliminar la asignación?`)) return; await eliminar(e.id); }

  return (
    <>
      <PageMeta title="Asignaciones | DATALAN" description="Asignación de activos a técnicos" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Asignaciones</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("asignaciones.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nueva</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>{["Activo", "Técnico", "Proyecto", "F. Asignación", "F. Devolución", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((e) => (<TableRow key={e.id}>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.activo_rel?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.tecnico?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.proyecto?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.fecha_asignacion ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.fecha_devolucion ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-right text-theme-sm">
                  {puede("asignaciones.editar") && <button onClick={() => abrirEditar(e)} className="mr-3 text-brand-500 hover:underline">Editar</button>}
                  {puede("asignaciones.eliminar") && <button onClick={() => borrar(e)} className="text-error-500 hover:underline">Eliminar</button>}
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
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar asignación" : "Nueva asignación"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Activo *</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.activo_id} onChange={(e) => setForm({ ...form, activo_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoAct ? (<option value="" disabled>Cargando...</option>) : activos.map((a) => (<option key={a.id} value={a.id}>{a.nombre}</option>))}
              </select>
              {errores.activo_id && <p className="mt-1 text-xs text-error-500">{errores.activo_id[0]}</p>}
            </div>
            <div><Label>Técnico</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.tecnico_id} onChange={(e) => setForm({ ...form, tecnico_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoTec ? (<option value="" disabled>Cargando...</option>) : tecnicos.map((t) => (<option key={t.id} value={t.id}>{t.nombre}</option>))}
              </select>
            </div>
          </div>
          <div><Label>Proyecto</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.proyecto_id} onChange={(e) => setForm({ ...form, proyecto_id: e.target.value })}>
              <option value="">Seleccione...</option>
              {cargandoProy ? (<option value="" disabled>Cargando...</option>) : proyectos.map((p) => (<option key={p.id} value={p.id}>{p.nombre}</option>))}
            </select>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Fecha asignación</Label><input type="date" className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.fecha_asignacion} onChange={(e) => setForm({ ...form, fecha_asignacion: e.target.value })} /></div>
            <div><Label>Fecha devolución</Label><input type="date" className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.fecha_devolucion} onChange={(e) => setForm({ ...form, fecha_devolucion: e.target.value })} /></div>
          </div>
          <div>
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
