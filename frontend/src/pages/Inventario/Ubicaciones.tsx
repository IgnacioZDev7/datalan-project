import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import { Acciones, BotonAccion, IconoEditar, IconoEliminar } from "../../components/common/TablaAcciones";
import { Modal } from "../../components/ui/modal";
import Button from "../../components/ui/button/Button";
import Input from "../../components/form/input/InputField";
import Label from "../../components/form/Label";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface Ubicacion { id: number; almacen_id: number; codigo: string; descripcion: string | null; almacen?: { nombre: string }; }

const FORM_VACIO = { almacen_id: "", codigo: "", descripcion: "" };

export default function Ubicaciones() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Ubicacion>("/ubicaciones");
  const { isOpen, openModal, closeModal } = useModal();
  const { items: almacenes, cargando: cargandoAlm } = useLookup("/almacenes");
  const [editando, setEditando] = useState<Ubicacion | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(p: Ubicacion) { setEditando(p); setForm({ almacen_id: String(p.almacen_id), codigo: p.codigo, descripcion: p.descripcion ?? "" }); setErrores({}); openModal(); }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const datos = { ...form, almacen_id: Number(form.almacen_id) };
      if (editando) await actualizar(editando.id, datos); else await crear(datos); closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(p: Ubicacion) { if (!confirm(`¿Eliminar "${p.codigo}"?`)) return; await eliminar(p.id); }

  return (
    <>
      <PageMeta title="Ubicaciones | DATALAN" description="Inventario" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Ubicaciones</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("ubicaciones.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 bg-gray-50 dark:bg-white/[0.02] dark:border-gray-800">
              <TableRow>{["Código", "Almacén", "Descripción", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((p) => (<TableRow key={p.id} className="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <TableCell className="px-5 py-4 font-medium text-gray-800 text-theme-sm dark:text-white/90">{p.codigo}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.almacen?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.descripcion ?? "-"}</TableCell>
                <TableCell className="px-5 py-4">
                  <Acciones>
                    {puede("ubicaciones.editar") && <BotonAccion titulo="Editar" color="brand" onClick={() => abrirEditar(p)}>{IconoEditar}</BotonAccion>}
                    {puede("ubicaciones.eliminar") && <BotonAccion titulo="Eliminar" color="error" onClick={() => borrar(p)}>{IconoEliminar}</BotonAccion>}
                  </Acciones>
                </TableCell>
              </TableRow>))}
            </TableBody>
          </Table>
        </div>
      </div>
      {meta && meta.last_page > 1 && (<div className="flex items-center justify-between mt-4 text-sm text-gray-500">
        <span>{meta.total} resultados — página {meta.current_page} de {meta.last_page}</span>
        <div className="flex gap-2">
          <button disabled={meta.current_page <= 1} onClick={() => irAPagina(meta.current_page - 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Anterior</button>
          <button disabled={meta.current_page >= meta.last_page} onClick={() => irAPagina(meta.current_page + 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Siguiente</button>
        </div>
      </div>)}
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-lg p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar ubicación" : "Nueva ubicación"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div><Label>Almacén *</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.almacen_id} onChange={(e) => setForm({ ...form, almacen_id: e.target.value })}>
              <option value="">Seleccione...</option>{cargandoAlm ? (<option value="" disabled>Cargando...</option>) : almacenes.map((a) => (<option key={a.id} value={a.id}>{a.nombre}</option>))}
            </select>
            {errores.almacen_id && <p className="mt-1 text-xs text-error-500">{errores.almacen_id[0]}</p>}
          </div>
          <div><Label>Código *</Label><Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />{errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}</div>
          <div><Label>Descripción</Label><Input value={form.descripcion} onChange={(e) => setForm({ ...form, descripcion: e.target.value })} /></div>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
