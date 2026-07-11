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

interface Categoria {
  id: number;
  nombre: string;
  tipo_inventario: string | null;
  categoria_padre_id: number | null;
  activo: boolean;
}

const FORM_VACIO = {
  nombre: "",
  tipo_inventario: "",
  categoria_padre_id: "",
  descripcion: "",
  activo: true,
};

export default function Categorias() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Categoria>("/categorias");
  const { isOpen, openModal, closeModal } = useModal();

  const { items: categoriasPadre, cargando: cargandoCat } = useLookup("/categorias");
  const [editando, setEditando] = useState<Categoria | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() {
    setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal();
  }

  function abrirEditar(p: Categoria) {
    setEditando(p);
    setForm({
      nombre: p.nombre,
      tipo_inventario: p.tipo_inventario ?? "",
      categoria_padre_id: p.categoria_padre_id ? String(p.categoria_padre_id) : "",
      descripcion: "",
      activo: p.activo,
    });
    setErrores({}); openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const datos = {
        ...form,
        categoria_padre_id: form.categoria_padre_id ? Number(form.categoria_padre_id) : null,
      };
      if (editando) { await actualizar(editando.id, datos); }
      else { await crear(datos); }
      closeModal();
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>;
      setErrores(axErr.response?.data?.errors ?? {});
    } finally { setGuardando(false); }
  }

  async function borrar(p: Categoria) {
    if (!confirm(`¿Eliminar la categoría "${p.nombre}"?`)) return;
    await eliminar(p.id);
  }

  return (
    <>
      <PageMeta title="Categorías | DATALAN" description="Catálogo de categorías" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Categorías</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar por nombre..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("categorias.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>
                {["Nombre", "Tipo Inventario", "Estado", ""].map((h) => (
                  <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>
              ) : items.length === 0 ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>
              ) : (
                items.map((p) => (
                  <TableRow key={p.id}>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{p.nombre}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.tipo_inventario ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-theme-sm">
                      <span className={p.activo ? "text-success-600" : "text-gray-400"}>{p.activo ? "Activo" : "Inactivo"}</span>
                    </TableCell>
                    <TableCell className="px-5 py-4 text-right text-theme-sm">
                      {puede("categorias.editar") && <button onClick={() => abrirEditar(p)} className="mr-3 text-brand-500 hover:underline">Editar</button>}
                      {puede("categorias.eliminar") && <button onClick={() => borrar(p)} className="text-error-500 hover:underline">Eliminar</button>}
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
          <span>{meta.total} resultados — página {meta.current_page} de {meta.last_page}</span>
          <div className="flex gap-2">
            <button disabled={meta.current_page <= 1} onClick={() => irAPagina(meta.current_page - 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Anterior</button>
            <button disabled={meta.current_page >= meta.last_page} onClick={() => irAPagina(meta.current_page + 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Siguiente</button>
          </div>
        </div>
      )}
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-lg p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
          {editando ? "Editar categoría" : "Nueva categoría"}
        </h2>
        <form onSubmit={guardar} className="space-y-4">
          <div><Label>Nombre *</Label><Input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />{errores.nombre && <p className="mt-1 text-xs text-error-500">{errores.nombre[0]}</p>}</div>
          <div><Label>Tipo inventario</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              value={form.tipo_inventario} onChange={(e) => setForm({ ...form, tipo_inventario: e.target.value })}>
              <option value="">Seleccione...</option>
              <option value="consumible">Consumible</option>
              <option value="activo_fijo">Activo fijo</option>
              <option value="herramienta">Herramienta</option>
              <option value="insumo">Insumo</option>
            </select>
          </div>
          <div><Label>Categoría padre</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              value={form.categoria_padre_id} onChange={(e) => setForm({ ...form, categoria_padre_id: e.target.value })}>
              <option value="">Ninguna</option>
              {cargandoCat ? (<option value="" disabled>Cargando...</option>) : categoriasPadre.filter(c => c.id !== editando?.id).map((c) => (
                <option key={c.id} value={c.id}>{c.nombre}</option>
              ))}
            </select>
            {errores.categoria_padre_id && <p className="mt-1 text-xs text-error-500">{errores.categoria_padre_id[0]}</p>}
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
