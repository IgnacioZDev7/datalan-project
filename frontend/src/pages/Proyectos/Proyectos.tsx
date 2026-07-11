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
import CamposDireccion from "../../components/form/CamposDireccion";
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from "../../components/ui/table";

interface Proyecto {
  id: number;
  codigo: string;
  nombre: string;
  empresa_id: number | null;
  tecnico_id: number | null;
  direccion_id: number | null;
  estado: string | null;
  fecha_inicio: string | null;
  fecha_fin: string | null;
  observaciones: string | null;
  direccion?: { ciudad: string; zona: string; calle: string; nro: string; referencia: string };
}

const DIR_VACIO = { ciudad: "", zona: "", calle: "", nro: "", referencia: "" };
const FORM_VACIO = { codigo: "", nombre: "", empresa_id: "", tecnico_id: "", direccion: DIR_VACIO, estado: "activo", fecha_inicio: "", fecha_fin: "", observaciones: "" };

export default function Proyectos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Proyecto>("/proyectos");
  const { isOpen, openModal, closeModal } = useModal();
  const { items: empresas, cargando: cargandoEmp } = useLookup("/empresas");
  const { items: tecnicos, cargando: cargandoTec } = useLookup("/tecnicos");
  const [editando, setEditando] = useState<Proyecto | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(e: Proyecto) {
    setEditando(e);
    setForm({
      codigo: e.codigo, nombre: e.nombre, empresa_id: String(e.empresa_id ?? ""), tecnico_id: String(e.tecnico_id ?? ""),
      direccion: e.direccion ?? DIR_VACIO, estado: e.estado ?? "activo", fecha_inicio: e.fecha_inicio ?? "",
      fecha_fin: e.fecha_fin ?? "", observaciones: e.observaciones ?? "",
    });
    setErrores({}); openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const { direccion, ...datos } = form;
      const payload = {
        ...datos,
        empresa_id: datos.empresa_id ? Number(datos.empresa_id) : null,
        tecnico_id: datos.tecnico_id ? Number(datos.tecnico_id) : null,
        direccion: direccion.ciudad || direccion.zona || direccion.calle || direccion.nro ? direccion : undefined,
      };
      if (editando) await actualizar(editando.id, payload); else await crear(payload);
      closeModal();
    } catch (err) { const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>; setErrores(axErr.response?.data?.errors ?? {}); }
    finally { setGuardando(false); }
  }

  async function borrar(e: Proyecto) { if (!confirm(`Eliminar "${e.nombre}"?`)) return; await eliminar(e.id); }

  return (
    <>
      <PageMeta title="Proyectos | DATALAN" description="Proyectos" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Proyectos</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("proyectos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>{["Código", "Nombre", "Estado", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((e) => (<TableRow key={e.id}>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.codigo}</TableCell>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{e.nombre}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><span className={e.estado === "activo" ? "text-success-600" : "text-gray-400"}>{e.estado ?? "-"}</span></TableCell>
                <TableCell className="px-5 py-4 text-right text-theme-sm">
                  {puede("proyectos.editar") && <button onClick={() => abrirEditar(e)} className="mr-3 text-brand-500 hover:underline">Editar</button>}
                  {puede("proyectos.eliminar") && <button onClick={() => borrar(e)} className="text-error-500 hover:underline">Eliminar</button>}
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
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar proyecto" : "Nuevo proyecto"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Código *</Label><Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />{errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}</div>
            <div><Label>Nombre *</Label><Input value={form.nombre} onChange={(e) => setForm({ ...form, nombre: e.target.value })} />{errores.nombre && <p className="mt-1 text-xs text-error-500">{errores.nombre[0]}</p>}</div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Empresa</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.empresa_id} onChange={(e) => setForm({ ...form, empresa_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoEmp ? (<option value="" disabled>Cargando...</option>) : empresas.map((e) => (<option key={e.id} value={e.id}>{e.nombre}</option>))}
              </select>
            </div>
            <div><Label>Técnico</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.tecnico_id} onChange={(e) => setForm({ ...form, tecnico_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoTec ? (<option value="" disabled>Cargando...</option>) : tecnicos.map((t) => (<option key={t.id} value={t.id}>{t.nombre}</option>))}
              </select>
            </div>
          </div>
          <CamposDireccion value={form.direccion} onChange={(field, val) => setForm({ ...form, direccion: { ...form.direccion, [field]: val } })} errores={errores} />
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Fecha inicio</Label><input type="date" className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.fecha_inicio} onChange={(e) => setForm({ ...form, fecha_inicio: e.target.value })} /></div>
            <div><Label>Fecha fin</Label><input type="date" className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.fecha_fin} onChange={(e) => setForm({ ...form, fecha_fin: e.target.value })} />{errores.fecha_fin && <p className="mt-1 text-xs text-error-500">{errores.fecha_fin[0]}</p>}</div>
          </div>
          <div><Label>Estado</Label>
            <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.estado} onChange={(e) => setForm({ ...form, estado: e.target.value })}>
              <option value="activo">Activo</option>
              <option value="finalizado">Finalizado</option>
              <option value="suspendido">Suspendido</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
          <div><Label>Observaciones</Label><Input value={form.observaciones} onChange={(e) => setForm({ ...form, observaciones: e.target.value })} /></div>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
