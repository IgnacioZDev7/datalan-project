import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import { ActivoBadge } from "../../components/common/EstadoBadge";
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

interface Tecnico {
  id: number; nombre: string; ci: string | null;
  telefono: string | null; cargo: string | null;
  usuario_id: number | null; activo: boolean;
}

const FORM_VACIO = { nombre: "", ci: "", telefono: "", cargo: "", usuario_id: "", activo: true };

export default function Tecnicos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Tecnico>("/tecnicos");
  const { isOpen, openModal, closeModal } = useModal();
  const [editando, setEditando] = useState<Tecnico | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(p: Tecnico) { setEditando(p); setForm({ nombre: p.nombre, ci: p.ci ?? "", telefono: p.telefono ?? "", cargo: p.cargo ?? "", usuario_id: p.usuario_id ? String(p.usuario_id) : "", activo: p.activo }); setErrores({}); openModal(); }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      if (editando) await actualizar(editando.id, form); else await crear(form); closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(p: Tecnico) { if (!confirm(`¿Eliminar a "${p.nombre}"?`)) return; await eliminar(p.id); }

  return (
    <>
      <PageMeta title="Técnicos | DATALAN" description="Terceros" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Técnicos</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("tecnicos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 bg-gray-50 dark:bg-white/[0.02] dark:border-gray-800">
              <TableRow>{["Nombre", "CI", "Cargo", "Teléfono", "Estado", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((p) => (<TableRow key={p.id} className="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <TableCell className="px-5 py-4 font-medium text-gray-800 text-theme-sm dark:text-white/90">{p.nombre}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.ci ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.cargo ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{p.telefono ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><ActivoBadge activo={p.activo} /></TableCell>
                <TableCell className="px-5 py-4">
                  <Acciones>
                    {puede("tecnicos.editar") && <BotonAccion titulo="Editar" color="brand" onClick={() => abrirEditar(p)}>{IconoEditar}</BotonAccion>}
                    {puede("tecnicos.eliminar") && <BotonAccion titulo="Eliminar" color="error" onClick={() => borrar(p)}>{IconoEliminar}</BotonAccion>}
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
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar técnico" : "Nuevo técnico"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div><Label>Nombre *</Label><Input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />{errores.nombre && <p className="mt-1 text-xs text-error-500">{errores.nombre[0]}</p>}</div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>CI</Label><Input value={form.ci} onChange={(e) => setForm({ ...form, ci: e.target.value })} />{errores.ci && <p className="mt-1 text-xs text-error-500">{errores.ci[0]}</p>}</div>
            <div><Label>Teléfono</Label><Input value={form.telefono} onChange={(e) => setForm({ ...form, telefono: e.target.value })} /></div>
          </div>
          <div><Label>Cargo</Label><Input value={form.cargo} onChange={(e) => setForm({ ...form, cargo: e.target.value })} /></div>
          <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input type="checkbox" checked={form.activo} onChange={(e) => setForm({ ...form, activo: e.target.checked })} /> Activo</label>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
