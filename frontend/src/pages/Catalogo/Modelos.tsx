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

interface Opcion { id: number; nombre: string; }
interface Modelo {
  id: number;
  marca_id: number;
  nombre: string;
  marca?: { nombre: string };
}

const FORM_VACIO = { marca_id: "", nombre: "", descripcion: "" };

export default function Modelos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Modelo>("/modelos");
  const { isOpen, openModal, closeModal } = useModal();

  const [marcas, setMarcas] = useState<Opcion[]>([]);
  const [editando, setEditando] = useState<Modelo | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  useEffect(() => {
    api.get("/marcas", { params: { per_page: 100 } }).then((r) => setMarcas(r.data.data));
  }, []);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }

  function abrirEditar(p: Modelo) {
    setEditando(p); setForm({ marca_id: String(p.marca_id), nombre: p.nombre, descripcion: "" }); setErrores({}); openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const datos = { ...form, marca_id: Number(form.marca_id) };
      if (editando) { await actualizar(editando.id, datos); } else { await crear(datos); }
      closeModal();
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>;
      setErrores(axErr.response?.data?.errors ?? {});
    } finally { setGuardando(false); }
  }

  async function borrar(p: Modelo) {
    if (!confirm(`¿Eliminar el modelo "${p.nombre}"?`)) return;
    await eliminar(p.id);
  }

  return (
    <>
      <PageMeta title="Modelos | DATALAN" description="Catálogo de modelos" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Modelos</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("modelos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>
                {["Nombre", "Marca", ""].map((h) => (
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
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.marca?.nombre ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-right text-theme-sm">
                      {puede("modelos.editar") && <button onClick={() => abrirEditar(p)} className="mr-3 text-brand-500 hover:underline">Editar</button>}
                      {puede("modelos.eliminar") && <button onClick={() => borrar(p)} className="text-error-500 hover:underline">Eliminar</button>}
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
          {editando ? "Editar modelo" : "Nuevo modelo"}
        </h2>
        <form onSubmit={guardar} className="space-y-4">
          <div>
            <Label>Marca *</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              value={form.marca_id} onChange={(e) => setForm({ ...form, marca_id: e.target.value })}>
              <option value="">Seleccione...</option>
              {marcas.map((m) => (<option key={m.id} value={m.id}>{m.nombre}</option>))}
            </select>
            {errores.marca_id && <p className="mt-1 text-xs text-error-500">{errores.marca_id[0]}</p>}
          </div>
          <div><Label>Nombre *</Label><Input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />{errores.nombre && <p className="mt-1 text-xs text-error-500">{errores.nombre[0]}</p>}</div>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
