import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import { useAuth } from "../../context/AuthContext";
import LabelModal from "../../components/common/LabelModal";
import EstadoBadge from "../../components/common/EstadoBadge";
import { Acciones, BotonAccion, IconoEtiqueta, IconoEditar, IconoEliminar } from "../../components/common/TablaAcciones";
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

interface Activo {
  id: number;
  producto_id: number;
  codigo_interno: string;
  nro_serie: string | null;
  mac: string | null;
  estado: string;
  situacion: string;
  ubicacion_id: number | null;
  tecnico_id: number | null;
  observaciones: string | null;
  producto?: { codigo: string; nombre: string; modelo?: { nombre: string } };
  ubicacion?: { codigo: string };
  tecnico?: { nombre: string };
}

const ESTADOS = [
  { v: "nuevo", t: "Nuevo" },
  { v: "bueno", t: "Bueno" },
  { v: "regular", t: "Regular" },
  { v: "danado", t: "Dañado" },
  { v: "en_reparacion", t: "En reparación" },
  { v: "baja", t: "Baja" },
];
const SITUACIONES = [
  { v: "en_almacen", t: "En almacén" },
  { v: "asignado", t: "Asignado" },
  { v: "instalado", t: "Instalado" },
  { v: "de_baja", t: "De baja" },
];

const FORM_VACIO = {
  producto_id: "",
  codigo_interno: "",
  nro_serie: "",
  mac: "",
  estado: "bueno",
  situacion: "en_almacen",
  ubicacion_id: "",
  tecnico_id: "",
  observaciones: "",
};

const selectCls =
  "h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90";

export default function Activos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, crear, actualizar, eliminar } =
    useCrud<Activo>("/activos");
  const { isOpen, openModal, closeModal } = useModal();

  const { items: productos, cargando: cargandoProd } = useLookup("/productos");
  const { items: ubicaciones, cargando: cargandoUbi } = useLookup("/ubicaciones");
  const { items: tecnicos, cargando: cargandoTec } = useLookup("/tecnicos");
  const [editando, setEditando] = useState<Activo | null>(null);
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);
  const { isOpen: isOpenLabel, openModal: openLabel, closeModal: closeLabel } = useModal();
  const [labelItem, setLabelItem] = useState<Activo | null>(null);

  function abrirNuevo() { setEditando(null); setForm(FORM_VACIO); setErrores({}); openModal(); }
  function abrirEditar(a: Activo) {
    setEditando(a);
    setForm({
      producto_id: String(a.producto_id ?? ""),
      codigo_interno: a.codigo_interno,
      nro_serie: a.nro_serie ?? "",
      mac: a.mac ?? "",
      estado: a.estado,
      situacion: a.situacion,
      ubicacion_id: String(a.ubicacion_id ?? ""),
      tecnico_id: String(a.tecnico_id ?? ""),
      observaciones: a.observaciones ?? "",
    });
    setErrores({}); openModal();
  }

  async function guardar(e: FormEvent) {
    e.preventDefault(); setGuardando(true); setErrores({});
    try {
      const datos = {
        ...form,
        producto_id: form.producto_id ? Number(form.producto_id) : null,
        ubicacion_id: form.ubicacion_id ? Number(form.ubicacion_id) : null,
        tecnico_id: form.tecnico_id ? Number(form.tecnico_id) : null,
        nro_serie: form.nro_serie || null,
        mac: form.mac || null,
        observaciones: form.observaciones || null,
      };
      if (editando) await actualizar(editando.id, datos); else await crear(datos);
      closeModal();
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]> }>;
      setErrores(axErr.response?.data?.errors ?? {});
    } finally { setGuardando(false); }
  }

  async function borrar(a: Activo) {
    if (!confirm(`¿Eliminar el activo "${a.codigo_interno}"?`)) return;
    await eliminar(a.id);
  }

  const err = (campo: string) => errores[campo] && <p className="mt-1 text-xs text-error-500">{errores[campo][0]}</p>;

  return (
    <>
      <PageMeta title="Activos | DATALAN" description="Inventario de activos y herramientas" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Activos</h1>
        <div className="flex gap-3">
          <input type="text" placeholder="Buscar código, serie o MAC..." className="h-11 w-full sm:w-64 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.buscar as string) ?? ""} onChange={(e) => filtrar({ buscar: e.target.value })} />
          {puede("activos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo</Button>}
        </div>
      </div>

      <div className="overflow-hidden bg-white border border-gray-200 rounded-xl dark:bg-white/[0.03] dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 bg-gray-50 dark:bg-white/[0.02] dark:border-gray-800">
              <TableRow>{["Código", "Producto", "Modelo", "N° Serie", "Estado", "Situación", ""].map((h) => (
                <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((a) => (<TableRow key={a.id} className="hover:bg-gray-50 dark:hover:bg-white/[0.03]">
                <TableCell className="px-5 py-4 font-medium text-gray-800 text-theme-sm dark:text-white/90">{a.codigo_interno}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm">
                  <div className="text-gray-800 dark:text-white/90">{a.producto?.nombre ?? "-"}</div>
                  <div className="text-gray-400 text-theme-xs">{a.producto?.codigo ?? ""}</div>
                </TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{a.producto?.modelo?.nombre ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{a.nro_serie ?? "-"}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><EstadoBadge valor={a.estado} /></TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><EstadoBadge valor={a.situacion} /></TableCell>
                <TableCell className="px-5 py-4">
                  <Acciones>
                    <BotonAccion titulo="Etiqueta QR" color="gray" onClick={() => { setLabelItem(a); openLabel(); }}>{IconoEtiqueta}</BotonAccion>
                    {puede("activos.editar") && <BotonAccion titulo="Editar" color="brand" onClick={() => abrirEditar(a)}>{IconoEditar}</BotonAccion>}
                    {puede("activos.eliminar") && <BotonAccion titulo="Eliminar" color="error" onClick={() => borrar(a)}>{IconoEliminar}</BotonAccion>}
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

      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-xl p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">{editando ? "Editar activo" : "Nuevo activo"}</h2>
        <form onSubmit={guardar} className="space-y-4">
          <div>
            <Label>Producto *</Label>
            <select className={selectCls} value={form.producto_id} onChange={(e) => setForm({ ...form, producto_id: e.target.value })}>
              <option value="">Seleccione...</option>
              {cargandoProd ? (<option value="" disabled>Cargando...</option>) : productos.map((p) => (<option key={p.id} value={p.id}>{p.codigo ? `${p.codigo} - ` : ""}{p.nombre}</option>))}
            </select>
            {err("producto_id")}
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Código interno *</Label><Input value={form.codigo_interno} onChange={(e) => setForm({ ...form, codigo_interno: e.target.value })} />{err("codigo_interno")}</div>
            <div><Label>N° de serie</Label><Input value={form.nro_serie} onChange={(e) => setForm({ ...form, nro_serie: e.target.value })} />{err("nro_serie")}</div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>MAC</Label><Input value={form.mac} onChange={(e) => setForm({ ...form, mac: e.target.value })} />{err("mac")}</div>
            <div><Label>Ubicación</Label>
              <select className={selectCls} value={form.ubicacion_id} onChange={(e) => setForm({ ...form, ubicacion_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoUbi ? (<option value="" disabled>Cargando...</option>) : ubicaciones.map((u) => (<option key={u.id} value={u.id}>{u.codigo ?? u.nombre ?? `Ubicación #${u.id}`}</option>))}
              </select>
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div><Label>Estado *</Label>
              <select className={selectCls} value={form.estado} onChange={(e) => setForm({ ...form, estado: e.target.value })}>
                {ESTADOS.map((o) => (<option key={o.v} value={o.v}>{o.t}</option>))}
              </select>
              {err("estado")}
            </div>
            <div><Label>Situación *</Label>
              <select className={selectCls} value={form.situacion} onChange={(e) => setForm({ ...form, situacion: e.target.value })}>
                {SITUACIONES.map((o) => (<option key={o.v} value={o.v}>{o.t}</option>))}
              </select>
              {err("situacion")}
            </div>
          </div>
          {form.situacion === "asignado" && (
            <div><Label>Técnico (requerido si está asignado) *</Label>
              <select className={selectCls} value={form.tecnico_id} onChange={(e) => setForm({ ...form, tecnico_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoTec ? (<option value="" disabled>Cargando...</option>) : tecnicos.map((t) => (<option key={t.id} value={t.id}>{t.nombre}</option>))}
              </select>
              {err("tecnico_id")}
            </div>
          )}
          <div><Label>Observaciones</Label><Input value={form.observaciones} onChange={(e) => setForm({ ...form, observaciones: e.target.value })} />{err("observaciones")}</div>
          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Guardar"}</Button>
          </div>
        </form>
      </Modal>

      {labelItem && (
        <LabelModal
          isOpen={isOpenLabel}
          onClose={closeLabel}
          codigo={labelItem.codigo_interno}
          nombre={labelItem.producto?.nombre ?? labelItem.codigo_interno}
        />
      )}
    </>
  );
}
