import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import Badge from "../../components/ui/badge/Badge";
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

interface Usuario {
  id: number; nombres: string; apellido_paterno: string | null;
  apellido_materno: string | null; ci: string | null;
  correo_electronico: string; cargo: string | null;
  telefono: string | null; activo: boolean;
  roles: string[];
}



const FORM_VACIO = {
  nombres: "", apellido_paterno: "", apellido_materno: "",
  ci: "", correo_electronico: "", contrasena: "",
  cargo: "", telefono: "", activo: true, roles: [] as string[],
};

export default function Usuarios() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Usuario>("/usuarios");
  const { isOpen, openModal, closeModal } = useModal();
  const { items: roles, cargando: cargandoRoles } = useLookup("/roles");

  const [editando, setEditando] = useState<Usuario | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() {
    setEditando(null);
    setForm(FORM_VACIO);
    setErrores({});
    openModal();
  }

  function abrirEditar(u: Usuario) {
    setEditando(u);
    setForm({
      nombres: u.nombres,
      apellido_paterno: u.apellido_paterno ?? "",
      apellido_materno: u.apellido_materno ?? "",
      ci: u.ci ?? "",
      correo_electronico: u.correo_electronico,
      contrasena: "",
      cargo: u.cargo ?? "",
      telefono: u.telefono ?? "",
      activo: u.activo,
      roles: u.roles ?? [],
    });
    setErrores({});
    openModal();
  }

  function toggleRol(nombre: string) {
    setForm((f) => ({
      ...f,
      roles: f.roles.includes(nombre)
        ? f.roles.filter((r) => r !== nombre)
        : [...f.roles, nombre],
    }));
  }

  async function guardar(e: FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setErrores({});
    const payload: Record<string, unknown> = {
      nombres: form.nombres,
      apellido_paterno: form.apellido_paterno || undefined,
      apellido_materno: form.apellido_materno || undefined,
      ci: form.ci || undefined,
      correo_electronico: form.correo_electronico,
      cargo: form.cargo || undefined,
      telefono: form.telefono || undefined,
      activo: form.activo,
      roles: form.roles.length > 0 ? form.roles : undefined,
    };
    if (!editando) payload.contrasena = form.contrasena;
    else if (form.contrasena) payload.contrasena = form.contrasena;
    try {
      if (editando) await actualizar(editando.id, payload);
      else await crear(payload);
      closeModal();
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>;
      setErrores(axErr.response?.data?.errors ?? {});
    } finally {
      setGuardando(false);
    }
  }

  async function desactivar(u: Usuario) {
    if (!confirm(`¿Desactivar a "${u.nombres}"?`)) return;
    await eliminar(u.id);
  }

  return (
    <>
      <PageMeta title="Usuarios | DATALAN" description="Administración" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Usuarios</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90" value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("usuarios.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 bg-gray-50 dark:bg-white/[0.02] dark:border-gray-800">
              <TableRow>{["Nombres", "Correo", "CI", "Cargo", "Roles", "Estado", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((u) => (<TableRow key={u.id} className="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <TableCell className="px-5 py-4 font-medium text-gray-800 text-theme-sm dark:text-white/90">{u.nombres} {u.apellido_paterno ?? ""}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{u.correo_electronico}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{u.ci ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{u.cargo ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm">
                  <div className="flex flex-wrap gap-1">
                    {u.roles?.length ? u.roles.map((r) => <Badge key={r} variant="light" color="primary" size="sm">{r}</Badge>) : <span className="text-gray-400">—</span>}
                  </div>
                </TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><ActivoBadge activo={u.activo} /></TableCell>
                <TableCell className="px-5 py-4">
                  <Acciones>
                    {puede("usuarios.editar") && <BotonAccion titulo="Editar" color="brand" onClick={() => abrirEditar(u)}>{IconoEditar}</BotonAccion>}
                    {u.activo && puede("usuarios.eliminar") && <BotonAccion titulo="Desactivar" color="error" onClick={() => desactivar(u)}>{IconoEliminar}</BotonAccion>}
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
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar usuario" : "Nuevo usuario"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div><Label>Nombres *</Label><Input value={form.nombres} onChange={(e) => setForm({ ...form, nombres: e.target.value })} />{errores.nombres && <p className="mt-1 text-xs text-error-500">{errores.nombres[0]}</p>}</div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Ap. paterno</Label><Input value={form.apellido_paterno} onChange={(e) => setForm({ ...form, apellido_paterno: e.target.value })} /></div>
            <div><Label>Ap. materno</Label><Input value={form.apellido_materno} onChange={(e) => setForm({ ...form, apellido_materno: e.target.value })} /></div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>CI</Label><Input value={form.ci} onChange={(e) => setForm({ ...form, ci: e.target.value })} />{errores.ci && <p className="mt-1 text-xs text-error-500">{errores.ci[0]}</p>}</div>
            <div><Label>Teléfono</Label><Input value={form.telefono} onChange={(e) => setForm({ ...form, telefono: e.target.value })} /></div>
          </div>
          <div><Label>Correo electrónico *</Label><Input type="email" value={form.correo_electronico} onChange={(e) => setForm({ ...form, correo_electronico: e.target.value })} />{errores.correo_electronico && <p className="mt-1 text-xs text-error-500">{errores.correo_electronico[0]}</p>}</div>
          <div><Label>{editando ? "Contraseña (dejar vacío para no cambiar)" : "Contraseña *"}</Label><Input type="password" value={form.contrasena} onChange={(e) => setForm({ ...form, contrasena: e.target.value })} />{errores.contrasena && <p className="mt-1 text-xs text-error-500">{errores.contrasena[0]}</p>}</div>
          <div><Label>Cargo</Label><Input value={form.cargo} onChange={(e) => setForm({ ...form, cargo: e.target.value })} /></div>
          <div>
            <Label>Roles</Label>
            <div className="flex flex-wrap gap-3 mt-1">
              {cargandoRoles ? <span className="text-sm text-gray-400">Cargando...</span> : roles.map((r) => (
                <label key={r.id} className="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                  <input type="checkbox" checked={form.roles.includes(r.nombre)} onChange={() => toggleRol(r.nombre)} />
                  {r.nombre}
                </label>
              ))}
            </div>
            {errores.roles && <p className="mt-1 text-xs text-error-500">{errores.roles[0]}</p>}
          </div>
          {editando && <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input type="checkbox" checked={form.activo} onChange={(e) => setForm({ ...form, activo: e.target.checked })} /> Activo</label>}
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
