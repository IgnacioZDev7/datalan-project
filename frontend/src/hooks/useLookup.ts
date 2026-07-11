import { useEffect, useState } from "react";
import api from "../services/api";

const cache = new Map<string, { data: unknown[]; timestamp: number }>();
const TTL = 5 * 60 * 1000;

export interface Opcion {
  id: number;
  nombre: string;
  abreviatura?: string;
  codigo?: string;
}

export function useLookup(endpoint: string) {
  const [items, setItems] = useState<Opcion[]>(
    (cache.get(endpoint)?.data as Opcion[]) ?? []
  );
  const [cargando, setCargando] = useState(!cache.has(endpoint));

  useEffect(() => {
    const cached = cache.get(endpoint);
    if (cached && Date.now() - cached.timestamp < TTL) {
      setItems(cached.data as Opcion[]);
      setCargando(false);
      return;
    }

    setCargando(true);
    api
      .get(endpoint, { params: { per_page: 200 } })
      .then((r) => {
        const data = r.data.data ?? [];
        cache.set(endpoint, { data, timestamp: Date.now() });
        setItems(data);
      })
      .finally(() => setCargando(false));
  }, [endpoint]);

  return { items, cargando };
}
