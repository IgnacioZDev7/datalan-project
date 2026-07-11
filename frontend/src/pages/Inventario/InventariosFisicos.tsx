import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
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

interface Conteo {
  id: number; codigo: string; almacen_id: number;
  fecha: string; estado: string; observaciones: string | null;
  detalles_count: number;
  detalles?: ConteoDetalle[];
}
interface ConteoDetalle {
  id: number; producto_id: number;
  cantidad_fisica: number; observaciones: string | null;
}
interface Linea { producto_id: string; cantidad_fisica: string; }

const LINEA_VACIA: Linea = { producto_id: "", cantidad_fisica: "" };
const ESTADOS: Record<string, string> = {
  en_proceso: "En proceso", cerrado: "Cerrado", anulado: "Anulado",
};
const COLORES: Record<string, string> = {
  en_proceso: "text-blue-600", cerrado: "text-success-600", anulado: "text-gray-400",
};

export default function InventariosFisicos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, eliminar, cargar } =
    useCrud<Conteo>("/inventarios-fisicos");
  const { openModal: openCrear, isOpen: isOpenCrear, closeModal: closeCrear } = useModal();
  const { openModal: openVer, isOpen: isOpenVer, closeModal: closeVer } = useModal();
  const { items: almacenes, cargando: cargandoAlm } = useLookup("/almacenes");
  const { items: productos, cargando: cargandoProd } = useLookup("/productos");

  const [form, setForm] = useState({
    codigo: `CTO-${Date.now()}`,
    almacen_id: "",
    fecha: new Date().toISOString().slice(0, 10),
    observaciones: "",
  });
  const [lineas, setLineas] = useState<Linea[]>([{ ...LINEA_VACIA }]);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);
  const [verDetalles, setVerDetalles] = useState<ConteoDetalle[]>([]);
  const [verCodigo, setVerCodigo] = useState("");

  function abrirNuevo() {
    setForm((f) => ({ ...f, codigo: `CTO-${Date.now()}`, almacen_id: "", fecha: new Date().toISOString().slice(0, 10), observaciones: "" }));
    setLineas([{ ...LINEA_VACIA }]);
    setErrores({});
    openCrear();
  }

  function setLinea(i: number, campo: keyof Linea, valor: string) {
    setLineas((ls) => ls.map((l, idx) => (idx === i ? { ...l, [campo]: valor } : l)));
  }

  async function guardar(e: FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setErrores({});
    const payload = {
      codigo: form.codigo,
      almacen_id: Number(form.almacen_id),
      fecha: form.fecha,
      observaciones: form.observaciones || undefined,
      detalles: lineas
        .filter((l) => l.producto_id)
        .map((l) => ({
          producto_id: Number(l.producto_id),
          cantidad_fisica: Number(l.cantidad_fisica),
        })),
    };
    try {
      await api.post("/inventarios-fisicos", payload);
      closeCrear();
      cargar();
    } catch (err) {
      const ax = err as AxiosError<{ errors?: Record<string, string[]>; message?: string }>;
      setErrores(ax.response?.data?.errors ?? { general: [ax.response?.data?.message ?? "Error al guardar."] });
    } finally {
      setGuardando(false);
    }
  }

  async function ver(c: Conteo) {
    setVerCodigo(c.codigo);
    if (c.detalles) {
      setVerDetalles(c.detalles);
    } else {
      try {
        const res = await api.get<{ data: Conteo }>(`/inventarios-fisicos/${c.id}`);
        setVerDetalles(res.data.data.detalles ?? []);
      } catch {
        setVerDetalles([]);
      }
    }
    openVer();
  }

  async function cerrar(c: Conteo) {
    if (!confirm(`¿Cerrar el conteo "${c.codigo}"? Se generará un ajuste con las diferencias.`)) return;
    try {
      await api.post(`/inventarios-fisicos/${c.id}/cerrar`);
      cargar();
    } catch { /* ignore */ }
  }

  async function anular(c: Conteo) {
    if (!confirm(`¿Anular el conteo "${c.codigo}"?`)) return;
    await eliminar(c.id);
  }

  function nombreAlmacen(id: number) {
    if (cargandoAlm) return id;
    return almacenes.find((a) => a.id === id)?.nombre ?? id;
  }

  function nombreProducto(id: number) {
    return productos.find((p) => p.id === id)?.nombre ?? `ID ${id}`;
  }

  return (
    <>
      <PageMeta title="Inventarios físicos | DATALAN" description="Conteo de inventario" />
      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Inventarios físicos</h1>
        <div className="flex gap-3">
          <select className="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90" value={(filtros.estado as string) ?? ""} onChange={(e) => filtrar({ estado: e.target.value })}>
            <option value="">Todos los estados</option>
            {Object.entries(ESTADOS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
          </select>
          {puede("inventarios_fisicos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo conteo</Button>}
        </div>
      </div>
      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>{["Código", "Almacén", "Fecha", "Estado", "Renglones", ""].map((h) => (<TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>))}</TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>)
              : items.length === 0 ? (<TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin resultados.</TableCell></TableRow>)
              : items.map((c) => (<TableRow key={c.id}>
                <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{c.codigo}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{nombreAlmacen(c.almacen_id)}</TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{String(c.fecha).slice(0, 10)}</TableCell>
                <TableCell className="px-5 py-4 text-theme-sm"><span className={COLORES[c.estado] ?? "text-gray-500"}>{ESTADOS[c.estado] ?? c.estado}</span></TableCell>
                <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{c.detalles_count}</TableCell>
                <TableCell className="px-5 py-4 text-right text-theme-sm">
                  <button onClick={() => ver(c)} className="mr-3 text-brand-500 hover:underline">Ver</button>
                  {c.estado === "en_proceso" && puede("inventarios_fisicos.editar") && <button onClick={() => cerrar(c)} className="mr-3 text-success-600 hover:underline">Cerrar</button>}
                  {c.estado === "en_proceso" && puede("inventarios_fisicos.eliminar") && <button onClick={() => anular(c)} className="text-error-500 hover:underline">Anular</button>}
                </TableCell>
              </TableRow>))}
            </TableBody>
          </Table>
        </div>
      </div>
      {meta && meta.last_page > 1 && (<div className="flex items-center justify-between mt-4 text-sm text-gray-500">
        <span>{meta.total} conteos — página {meta.current_page} de {meta.last_page}</span>
        <div className="flex gap-2">
          <button disabled={meta.current_page <= 1} onClick={() => irAPagina(meta.current_page - 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Anterior</button>
          <button disabled={meta.current_page >= meta.last_page} onClick={() => irAPagina(meta.current_page + 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Siguiente</button>
        </div>
      </div>)}

      {/* Modal nuevo conteo */}
      <Modal isOpen={isOpenCrear} onClose={closeCrear} className="max-w-3xl p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Nuevo conteo físico</h2>
        {errores.general && <div className="p-3 mb-3 text-sm rounded-lg text-error-600 bg-error-50 dark:bg-error-500/10">{errores.general[0]}</div>}
        <form onSubmit={guardar} className="space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <Label>Código *</Label>
              <Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />
              {errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}
            </div>
            <div>
              <Label>Almacén *</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.almacen_id} onChange={(e) => setForm({ ...form, almacen_id: e.target.value })}>
                <option value="">Seleccione...</option>
                {cargandoAlm ? (<option value="" disabled>Cargando...</option>) : almacenes.map((a) => <option key={a.id} value={a.id}>{a.nombre}</option>)}
              </select>
              {errores.almacen_id && <p className="mt-1 text-xs text-error-500">{errores.almacen_id[0]}</p>}
            </div>
            <div>
              <Label>Fecha *</Label>
              <Input type="date" value={form.fecha} onChange={(e) => setForm({ ...form, fecha: e.target.value })} />
            </div>
          </div>

          {/* Detalles */}
          <div>
            <div className="flex items-center justify-between mb-2">
              <Label>Detalle (productos)</Label>
              <button type="button" onClick={() => setLineas((l) => [...l, { ...LINEA_VACIA }])} className="text-sm text-brand-500 hover:underline">+ Agregar línea</button>
            </div>
            {errores.detalles && <p className="mb-2 text-xs text-error-500">{errores.detalles[0]}</p>}
            <div className="space-y-2">
              {lineas.map((l, i) => (
                <div key={i} className="grid items-center grid-cols-12 gap-2">
                  <select className="h-10 col-span-5 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={l.producto_id} onChange={(e) => setLinea(i, "producto_id", e.target.value)}>
                    <option value="">Producto...</option>
                    {cargandoProd ? (<option value="" disabled>Cargando...</option>) : productos.map((p) => <option key={p.id} value={p.id}>{p.codigo} - {p.nombre}</option>)}
                  </select>
                  <input placeholder="Cantidad física" type="number" step="0.01" className="h-10 col-span-3 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" value={l.cantidad_fisica} onChange={(e) => setLinea(i, "cantidad_fisica", e.target.value)} />
                  <span className="col-span-3 text-xs text-gray-400">Cantidad física</span>
                  <button type="button" onClick={() => setLineas((ls) => ls.filter((_, idx) => idx !== i))} className="col-span-1 text-error-500 hover:underline">✕</button>
                </div>
              ))}
            </div>
          </div>

          <div>
            <Label>Observaciones</Label>
            <Input value={form.observaciones} onChange={(e) => setForm({ ...form, observaciones: e.target.value })} />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeCrear}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Registrar"}</Button>
          </div>
        </form>
      </Modal>

      {/* Modal ver detalles */}
      <Modal isOpen={isOpenVer} onClose={closeVer} className="max-w-2xl p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Detalles — {verCodigo}</h2>
        {verDetalles.length === 0 ? (
          <p className="text-sm text-gray-500">Sin detalles.</p>
        ) : (
          <Table>
            <TableHeader>
              <TableRow>
                <TableCell isHeader className="px-4 py-2 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">Producto</TableCell>
                <TableCell isHeader className="px-4 py-2 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">Cantidad física</TableCell>
                <TableCell isHeader className="px-4 py-2 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">Observaciones</TableCell>
              </TableRow>
            </TableHeader>
            <TableBody>
              {verDetalles.map((d) => (
                <TableRow key={d.id}>
                  <TableCell className="px-4 py-2 text-gray-700 text-theme-sm dark:text-gray-300">{nombreProducto(d.producto_id)}</TableCell>
                  <TableCell className="px-4 py-2 text-gray-500 text-theme-sm dark:text-gray-400">{d.cantidad_fisica}</TableCell>
                  <TableCell className="px-4 py-2 text-gray-500 text-theme-sm dark:text-gray-400">{d.observaciones ?? "-"}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        )}
        <div className="flex justify-end pt-4">
          <Button size="sm" variant="outline" onClick={closeVer}>Cerrar</Button>
        </div>
      </Modal>
    </>
  );
}
