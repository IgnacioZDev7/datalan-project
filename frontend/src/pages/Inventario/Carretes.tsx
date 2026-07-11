import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
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

interface Carrete {
  id: number;
  codigo: string;
  activo_id: number | null;
  categoria_id: number | null;
  capacidad_maxima: number | null;
  activo: boolean;
  activo_rel?: { nombre: string };
  categoria?: { nombre: string };
}

const FORM_VACIO = { codigo: "", activo_id: "", categoria_id: "", capacidad_maxima: "", activo: true };

export default function Carretes() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Carrete>("/carretes");
  const { isOpen, openModal, closeModal } = useModal();

  const { items: activos, cargando: cargandoAct } = useLookup("/activos");
  const { items: categorias, cargando: cargandoCat } = useLookup("/categorias");
  const [editando, setEditando] = useState<Carrete | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(e: Carrete) {
    setEditando(e);
    setForm({
      codigo: e.codigo,
      activo_id: String(e.activo_id ?? ""),
      categoria_id: String(e.categoria_id ?? ""),
      capacidad_maxima: e.capacidad_maxima != null ? String(e.capacidad_maxima) : "",
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
        categoria_id: form.categoria_id ? Number(form.categoria_id) : null,
        capacidad_maxima: form.capacidad_maxima ? Number(form.capacidad_maxima) : null,
      };
      if (editando) await actualizar(editando.id, datos); else await crear(datos);
      closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(e: Carrete) { if (!confirm(`¿Eliminar el carrete "${e.codigo}"?`)) return; await eliminar(e.id); }

  return (
    <>
      <PageMeta title="Carretes | DATALAN" description="Inventario de carretes" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Carretes</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("carretes.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>{["Código", "Activo", "Categoría", "Cap. Máxima", "Estado", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((e) => (<TableRow key={e.id}>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.codigo}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.activo_rel?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.categoria?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{e.capacidad_maxima ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><span className={e.activo ? "text-success-600" : "text-gray-400"}>{e.activo ? "Activo" : "Inactivo"}</span></TableCell>
                <TableCell className="px-5 py-4 text-right text-theme-sm">
                  {puede("carretes.editar") && <button onClick={() => abrirEditar(e)} className="mr-3 text-brand-500 hover:underline">Editar</button>}
                  {puede("carretes.eliminar") && <button onClick={() => borrar(e)} className="text-error-500 hover:underline">Eliminar</button>}
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
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar carrete" : "Nuevo carrete"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Código *</Label><Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />{errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}</div>
            <div><Label>Capacidad máxima</Label><Input value={form.capacidad_maxima} onChange={(e) => setForm({ ...form, capacidad_maxima: e.target.value })} /></div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Activo</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.activo_id} onChange={(e) => setForm({ ...form, activo_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoAct ? (<option value="" disabled>Cargando...</option>) : activos.map((a) => (<option key={a.id} value={a.id}>{a.nombre}</option>))}
              </select>
            </div>
            <div><Label>Categoría</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.categoria_id} onChange={(e) => setForm({ ...form, categoria_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoCat ? (<option value="" disabled>Cargando...</option>) : categorias.map((c) => (<option key={c.id} value={c.id}>{c.nombre}</option>))}
              </select>
            </div>
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
