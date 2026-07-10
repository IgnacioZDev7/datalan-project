import { useCallback, useEffect, useState } from "react";
import api from "../services/api";

export interface Paginacion {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
}

/**
 * Hook genérico de CRUD para un endpoint. Lo reutilizan todas las pantallas de módulo.
 * - lista paginada con filtros (buscar, activo, page, per_page, etc.)
 * - crear / actualizar / eliminar (recargan la lista)
 */
export function useCrud<T = Record<string, unknown>>(
  endpoint: string,
  filtrosIniciales: Record<string, unknown> = {}
) {
  const [items, setItems] = useState<T[]>([]);
  const [meta, setMeta] = useState<Paginacion | null>(null);
  const [cargando, setCargando] = useState(false);
  const [filtros, setFiltros] = useState<Record<string, unknown>>({
    page: 1,
    per_page: 10,
    ...filtrosIniciales,
  });

  const cargar = useCallback(async () => {
    setCargando(true);
    try {
      const params = Object.fromEntries(
        Object.entries(filtros).filter(([, v]) => v !== "" && v != null)
      );
      const r = await api.get(endpoint, { params });
      setItems(r.data.data ?? []);
      setMeta(r.data.meta ?? null);
    } finally {
      setCargando(false);
    }
  }, [endpoint, filtros]);

  useEffect(() => {
    cargar();
  }, [cargar]);

  const crear = (data: Record<string, unknown>) => api.post(endpoint, data).then(cargar);
  const actualizar = (id: number, data: Record<string, unknown>) =>
    api.put(`${endpoint}/${id}`, data).then(cargar);
  const eliminar = (id: number) => api.delete(`${endpoint}/${id}`).then(cargar);

  /** Cambia un filtro y vuelve a la página 1. */
  const filtrar = (cambios: Record<string, unknown>) =>
    setFiltros((prev) => ({ ...prev, ...cambios, page: 1 }));

  const irAPagina = (page: number) => setFiltros((prev) => ({ ...prev, page }));

  return { items, meta, cargando, filtros, filtrar, irAPagina, cargar, crear, actualizar, eliminar };
}
