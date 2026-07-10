import { useEffect, useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useModal } from "../../hooks/useModal";
import api from "../../services/api";
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

// ---- Tipos ----
interface Opcion {
  id: number;
  nombre: string;
  abreviatura?: string;
}
interface Producto {
  id: number;
  codigo: string;
  nombre: string;
  categoria_id: number;
  unidad_id: number;
  retornable: boolean;
  activo: boolean;
  categoria?: { nombre: string };
  unidad?: { abreviatura: string };
}

const FORM_VACIO = {
  codigo: "",
  nombre: "",
  categoria_id: "",
  unidad_id: "",
  retornable: false,
  activo: true,
};

export default function Productos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Producto>("/productos");
  const { isOpen, openModal, closeModal } = useModal();

  const [categorias, setCategorias] = useState<Opcion[]>([]);
  const [unidades, setUnidades] = useState<Opcion[]>([]);
  const [editando, setEditando] = useState<Producto | null>(null);
  const [form, setForm] = useState<typeof FORM_VACIO>(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  // Carga las opciones de los selects.
  useEffect(() => {
    api.get("/categorias", { params: { per_page: 100 } }).then((r) => setCategorias(r.data.data));
    api.get("/unidades-medida", { params: { per_page: 100 } }).then((r) => setUnidades(r.data.data));
  }, []);

  function abrirNuevo() {
    setEditando(null);
    setForm(FORM_VACIO);
    setErrores({});
    openModal();
  }

  function abrirEditar(p: Producto) {
    setEditando(p);
    setForm({
      codigo: p.codigo,
      nombre: p.nombre,
      categoria_id: String(p.categoria_id),
      unidad_id: String(p.unidad_id),
      retornable: p.retornable,
      activo: p.activo,
    });
    setErrores({});
    openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setErrores({});
    try {
      const datos = { ...form, categoria_id: Number(form.categoria_id), unidad_id: Number(form.unidad_id) };
      if (editando) {
        await actualizar(editando.id, datos);
      } else {
        await crear(datos);
      }
      closeModal();
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>;
      setErrores(axErr.response?.data?.errors ?? {});
    } finally {
      setGuardando(false);
    }
  }

  async function borrar(p: Producto) {
    if (!confirm(`¿Eliminar el producto "${p.nombre}"?`)) return;
    await eliminar(p.id);
  }

  return (
    <>
      <PageMeta title="Productos | DATALAN" description="Catálogo de productos" />

      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Productos</h1>
        <div className="flex gap-3">
          <input
            type="text"
            placeholder="Buscar por código o nombre..."
            className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""}
            onChange={(e) => filtrar({ buscar: e.target.value })}
          />
          {puede("productos.crear") && (
            <Button size="sm" onClick={abrirNuevo}>
              + Nuevo
            </Button>
          )}
        </div>
      </div>

      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>
                {["Código", "Nombre", "Categoría", "Unidad", "Estado", ""].map((h) => (
                  <TableCell
                    key={h}
                    isHeader
                    className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400"
                  >
                    {h}
                  </TableCell>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (
                <TableRow>
                  <TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell>
                </TableRow>
              ) : items.length === 0 ? (
                <TableRow>
                  <TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell>
                </TableRow>
              ) : (
                items.map((p) => (
                  <TableRow key={p.id}>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{p.codigo}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{p.nombre}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.categoria?.nombre ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.unidad?.abreviatura ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-theme-sm">
                      <span className={p.activo ? "text-success-600" : "text-gray-400"}>
                        {p.activo ? "Activo" : "Inactivo"}
                      </span>
                    </TableCell>
                    <TableCell className="px-5 py-4 text-right text-theme-sm">
                      {puede("productos.editar") && (
                        <button onClick={() => abrirEditar(p)} className="mr-3 text-brand-500 hover:underline">
                          Editar
                        </button>
                      )}
                      {puede("productos.eliminar") && (
                        <button onClick={() => borrar(p)} className="text-error-500 hover:underline">
                          Eliminar
                        </button>
                      )}
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </div>
      </div>

      {/* Paginación */}
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
          <span>
            {meta.total} resultados — página {meta.current_page} de {meta.last_page}
          </span>
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

      {/* Modal crear/editar */}
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-lg p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
          {editando ? "Editar producto" : "Nuevo producto"}
        </h2>
        <form onSubmit={guardar} className="space-y-4">
          <div>
            <Label>Código *</Label>
            <Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />
            {errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}
          </div>
          <div>
            <Label>Nombre *</Label>
            <Input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />
            {errores.nombre && <p className="mt-1 text-xs text-error-500">{errores.nombre[0]}</p>}
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Categoría *</Label>
              <select
                className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                value={form.categoria_id}
                onChange={(e) => setForm({ ...form, categoria_id: e.target.value })}
              >
                <option value="">Seleccione...</option>
                {categorias.map((c) => (
                  <option key={c.id} value={c.id}>{c.nombre}</option>
                ))}
              </select>
              {errores.categoria_id && <p className="mt-1 text-xs text-error-500">{errores.categoria_id[0]}</p>}
            </div>
            <div>
              <Label>Unidad *</Label>
              <select
                className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                value={form.unidad_id}
                onChange={(e) => setForm({ ...form, unidad_id: e.target.value })}
              >
                <option value="">Seleccione...</option>
                {unidades.map((u) => (
                  <option key={u.id} value={u.id}>{u.nombre} ({u.abreviatura})</option>
                ))}
              </select>
              {errores.unidad_id && <p className="mt-1 text-xs text-error-500">{errores.unidad_id[0]}</p>}
            </div>
          </div>
          <div className="flex gap-6">
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" checked={form.retornable} onChange={(e) => setForm({ ...form, retornable: e.target.checked })} />
              Retornable
            </label>
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
              <input type="checkbox" checked={form.activo} onChange={(e) => setForm({ ...form, activo: e.target.checked })} />
              Activo
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
