import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import { useCrud } from "../../hooks/useCrud";
import { useLookup } from "../../hooks/useLookup";
import { useModal } from "../../hooks/useModal";
import api from "../../services/api";
import { useAuth } from "../../context/AuthContext";
import { downloadReport } from "../../utils/downloadReport";
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

interface Movimiento {
  id: number;
  codigo: string;
  tipo: string;
  fecha: string;
  almacen_origen_id: number | null;
  almacen_destino_id: number | null;
  anulado: boolean;
}
interface Linea {
  producto_id: string;
  cantidad: string;
  metraje: string;
  carrete_id: string;
}

const TIPOS = ["entrada", "salida", "devolucion", "baja", "traslado", "ajuste"];
const LINEA_VACIA: Linea = { producto_id: "", cantidad: "", metraje: "", carrete_id: "" };

export default function Movimientos() {
  const { puede } = useAuth();
  const { items, meta, cargando, filtros, filtrar, irAPagina, eliminar, cargar } =
    useCrud<Movimiento>("/movimientos");
  const { isOpen, openModal, closeModal } = useModal();

  const { items: almacenes, cargando: cargandoAlm } = useLookup("/almacenes");
  const { items: productos, cargando: cargandoProd } = useLookup("/productos");
  const { items: proveedores, cargando: cargandoProv } = useLookup("/proveedores");
  const { items: proyectos, cargando: cargandoProy } = useLookup("/proyectos");

  const [form, setForm] = useState({
    codigo: "",
    tipo: "entrada",
    fecha: new Date().toISOString().slice(0, 10),
    almacen_origen_id: "",
    almacen_destino_id: "",
    proveedor_id: "",
    proyecto_id: "",
    documento_referencia: "",
    observaciones: "",
  });
  const [lineas, setLineas] = useState<Linea[]>([{ ...LINEA_VACIA }]);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);

  const requiereOrigen = ["salida", "baja", "traslado"].includes(form.tipo);
  const requiereDestino = ["entrada", "devolucion", "traslado", "ajuste"].includes(form.tipo);

  function abrirNuevo() {
    setForm((f) => ({ ...f, codigo: `MOV-${Date.now()}` }));
    setLineas([{ ...LINEA_VACIA }]);
    setErrores({});
    openModal();
  }

  function setLinea(i: number, campo: keyof Linea, valor: string) {
    setLineas((ls) => ls.map((l, idx) => (idx === i ? { ...l, [campo]: valor } : l)));
  }

  async function guardar(e: FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setErrores({});
    const num = (v: string) => (v === "" ? undefined : Number(v));
    const payload = {
      codigo: form.codigo,
      tipo: form.tipo,
      fecha: form.fecha,
      almacen_origen_id: num(form.almacen_origen_id),
      almacen_destino_id: num(form.almacen_destino_id),
      proveedor_id: num(form.proveedor_id),
      proyecto_id: num(form.proyecto_id),
      documento_referencia: form.documento_referencia || undefined,
      observaciones: form.observaciones || undefined,
      detalles: lineas
        .filter((l) => l.producto_id)
        .map((l) => ({
          producto_id: Number(l.producto_id),
          cantidad: num(l.cantidad),
          metraje: num(l.metraje),
          carrete_id: num(l.carrete_id),
        })),
    };
    try {
      await api.post("/movimientos", payload);
      closeModal();
      cargar();
    } catch (err) {
      const ax = err as AxiosError<{ errors?: Record<string, string[]>; message?: string }>;
      setErrores(ax.response?.data?.errors ?? { general: [ax.response?.data?.message ?? "Error al guardar."] });
    } finally {
      setGuardando(false);
    }
  }

  async function anular(m: Movimiento) {
    if (!confirm(`¿Anular el movimiento ${m.codigo}? Se revertirán sus efectos en el inventario.`)) return;
    await eliminar(m.id);
  }

  return (
    <>
      <PageMeta title="Movimientos | DATALAN" description="Movimientos de inventario" />

      <div className="flex flex-col gap-4 mb-5 sm:flex-row sm:items-center sm:justify-between">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Movimientos</h1>
        <div className="flex gap-3">
          <select
            className="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90"
            value={(filtros.tipo as string) ?? ""}
            onChange={(e) => filtrar({ tipo: e.target.value })}
          >
            <option value="">Todos los tipos</option>
            {TIPOS.map((t) => <option key={t} value={t}>{t}</option>)}
          </select>
          {puede("movimientos.crear") && <Button size="sm" onClick={abrirNuevo}>+ Nuevo movimiento</Button>}
          {puede("reportes.exportar") && <>
            <Button size="sm" variant="outline" onClick={() => downloadReport("/reportes/movimientos/excel", "movimientos.xlsx", { tipo: filtros.tipo as string, desde: filtros.desde as string, hasta: filtros.hasta as string })}>Excel</Button>
            <Button size="sm" variant="outline" onClick={() => downloadReport("/reportes/movimientos/pdf", "movimientos.pdf", { tipo: filtros.tipo as string, desde: filtros.desde as string, hasta: filtros.hasta as string })}>PDF</Button>
          </>}
        </div>
      </div>

      <div className="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
        <div className="max-w-full overflow-x-auto">
          <Table>
            <TableHeader className="border-b border-gray-100 dark:border-gray-800">
              <TableRow>
                {["Código", "Tipo", "Fecha", "Origen", "Destino", "Estado", ""].map((h) => (
                  <TableCell key={h} isHeader className="px-5 py-3 font-medium text-gray-500 text-start text-theme-xs dark:text-gray-400">{h}</TableCell>
                ))}
              </TableRow>
            </TableHeader>
            <TableBody className="divide-y divide-gray-100 dark:divide-gray-800">
              {cargando ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Cargando...</TableCell></TableRow>
              ) : items.length === 0 ? (
                <TableRow><TableCell className="px-5 py-6 text-center text-gray-500">Sin movimientos.</TableCell></TableRow>
              ) : (
                items.map((m) => (
                  <TableRow key={m.id}>
                    <TableCell className="px-5 py-4 text-gray-700 text-theme-sm dark:text-gray-300">{m.codigo}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 capitalize text-theme-sm dark:text-gray-400">{m.tipo}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{String(m.fecha).slice(0, 10)}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{m.almacen_origen_id ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-gray-500 text-theme-sm dark:text-gray-400">{m.almacen_destino_id ?? "-"}</TableCell>
                    <TableCell className="px-5 py-4 text-theme-sm">
                      <span className={m.anulado ? "text-error-500" : "text-success-600"}>{m.anulado ? "Anulado" : "Vigente"}</span>
                    </TableCell>
                    <TableCell className="px-5 py-4 text-right text-theme-sm">
                      {!m.anulado && puede("movimientos.anular") && (
                        <button onClick={() => anular(m)} className="text-error-500 hover:underline">Anular</button>
                      )}
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
          <span>{meta.total} movimientos — página {meta.current_page} de {meta.last_page}</span>
          <div className="flex gap-2">
            <button disabled={meta.current_page <= 1} onClick={() => irAPagina(meta.current_page - 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Anterior</button>
            <button disabled={meta.current_page >= meta.last_page} onClick={() => irAPagina(meta.current_page + 1)} className="px-3 py-1 border rounded-lg disabled:opacity-40 dark:border-gray-700">Siguiente</button>
          </div>
        </div>
      )}

      {/* Modal nuevo movimiento */}
      <Modal isOpen={isOpen} onClose={closeModal} className="max-w-3xl p-6 m-4">
        <h2 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Nuevo movimiento</h2>
        {errores.general && <div className="p-3 mb-3 text-sm rounded-lg text-error-600 bg-error-50 dark:bg-error-500/10">{errores.general[0]}</div>}
        <form onSubmit={guardar} className="space-y-4">
          <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
              <Label>Código *</Label>
              <Input value={form.codigo} onChange={(e) => setForm({ ...form, codigo: e.target.value })} />
              {errores.codigo && <p className="mt-1 text-xs text-error-500">{errores.codigo[0]}</p>}
            </div>
            <div>
              <Label>Tipo *</Label>
              <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm capitalize dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.tipo} onChange={(e) => setForm({ ...form, tipo: e.target.value })}>
                {TIPOS.map((t) => <option key={t} value={t}>{t}</option>)}
              </select>
            </div>
            <div>
              <Label>Fecha *</Label>
              <Input type="date" value={form.fecha} onChange={(e) => setForm({ ...form, fecha: e.target.value })} />
            </div>
            {requiereOrigen && (
              <div>
                <Label>Almacén origen *</Label>
                <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.almacen_origen_id} onChange={(e) => setForm({ ...form, almacen_origen_id: e.target.value })}>
                  <option value="">Seleccione...</option>
                  {cargandoAlm ? (<option value="" disabled>Cargando...</option>) : almacenes.map((a) => <option key={a.id} value={a.id}>{a.nombre}</option>)}
                </select>
                {errores.almacen_origen_id && <p className="mt-1 text-xs text-error-500">{errores.almacen_origen_id[0]}</p>}
              </div>
            )}
            {requiereDestino && (
              <div>
                <Label>Almacén destino *</Label>
                <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.almacen_destino_id} onChange={(e) => setForm({ ...form, almacen_destino_id: e.target.value })}>
                  <option value="">Seleccione...</option>
                  {cargandoAlm ? (<option value="" disabled>Cargando...</option>) : almacenes.map((a) => <option key={a.id} value={a.id}>{a.nombre}</option>)}
                </select>
                {errores.almacen_destino_id && <p className="mt-1 text-xs text-error-500">{errores.almacen_destino_id[0]}</p>}
              </div>
            )}
            {form.tipo === "entrada" && (
              <div>
                <Label>Proveedor</Label>
                <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.proveedor_id} onChange={(e) => setForm({ ...form, proveedor_id: e.target.value })}>
                  <option value="">-</option>
                  {cargandoProv ? (<option value="" disabled>Cargando...</option>) : proveedores.map((p) => <option key={p.id} value={p.id}>{p.nombre}</option>)}
                </select>
              </div>
            )}
            {form.tipo === "salida" && (
              <div>
                <Label>Proyecto</Label>
                <select className="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" value={form.proyecto_id} onChange={(e) => setForm({ ...form, proyecto_id: e.target.value })}>
                  <option value="">-</option>
                  {cargandoProy ? (<option value="" disabled>Cargando...</option>) : proyectos.map((p) => <option key={p.id} value={p.id}>{p.nombre}</option>)}
                </select>
              </div>
            )}
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
                  <input placeholder="Cantidad" type="number" step="0.01" className="h-10 col-span-2 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" value={l.cantidad} onChange={(e) => setLinea(i, "cantidad", e.target.value)} />
                  <input placeholder="Metraje" type="number" step="0.01" className="h-10 col-span-2 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" value={l.metraje} onChange={(e) => setLinea(i, "metraje", e.target.value)} />
                  <input placeholder="Carrete ID" type="number" className="h-10 col-span-2 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" value={l.carrete_id} onChange={(e) => setLinea(i, "carrete_id", e.target.value)} />
                  <button type="button" onClick={() => setLineas((ls) => ls.filter((_, idx) => idx !== i))} className="col-span-1 text-error-500 hover:underline">✕</button>
                </div>
              ))}
            </div>
            <p className="mt-1 text-xs text-gray-400">Consumibles: cantidad. Cable: carrete ID + metraje.</p>
          </div>

          <div>
            <Label>Observaciones</Label>
            <Input value={form.observaciones} onChange={(e) => setForm({ ...form, observaciones: e.target.value })} />
          </div>

          <div className="flex justify-end gap-3 pt-2">
            <Button size="sm" variant="outline" onClick={closeModal}>Cancelar</Button>
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Registrar"}</Button>
          </div>
        </form>
      </Modal>
    </>
  );
}
