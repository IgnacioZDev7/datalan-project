import Badge from "../ui/badge/Badge";

type BadgeColor = React.ComponentProps<typeof Badge>["color"];

// Diccionario central de estados del sistema → color + etiqueta legible.
// Cubre: activo/inactivo, estado físico y situación de activos, y tipos de movimiento.
const MAPA: Record<string, { color: BadgeColor; label: string }> = {
  // Genéricos (activo/inactivo)
  activo: { color: "success", label: "Activo" },
  inactivo: { color: "light", label: "Inactivo" },

  // Estado FÍSICO de un activo
  nuevo: { color: "success", label: "Nuevo" },
  bueno: { color: "success", label: "Bueno" },
  regular: { color: "warning", label: "Regular" },
  danado: { color: "error", label: "Dañado" },
  en_reparacion: { color: "warning", label: "En reparación" },
  baja: { color: "error", label: "Baja" },

  // SITUACIÓN (ciclo de vida) de un activo
  en_almacen: { color: "info", label: "En almacén" },
  asignado: { color: "primary", label: "Asignado" },
  instalado: { color: "success", label: "Instalado" },
  de_baja: { color: "error", label: "De baja" },

  // Tipos de MOVIMIENTO
  entrada: { color: "success", label: "Entrada" },
  salida: { color: "error", label: "Salida" },
  devolucion: { color: "info", label: "Devolución" },
  traslado: { color: "primary", label: "Traslado" },
  ajuste: { color: "warning", label: "Ajuste" },

  // Estado de PROYECTO
  finalizado: { color: "info", label: "Finalizado" },
  suspendido: { color: "warning", label: "Suspendido" },
  cancelado: { color: "error", label: "Cancelado" },

  // Estado de CONTEO físico
  en_proceso: { color: "info", label: "En proceso" },
  cerrado: { color: "success", label: "Cerrado" },
  anulado: { color: "light", label: "Anulado" },
};

/** Píldora de color para un valor de estado/situación. */
export default function EstadoBadge({ valor }: { valor: string | null | undefined }) {
  if (!valor) return <span className="text-gray-400">—</span>;
  const info = MAPA[valor] ?? { color: "light" as BadgeColor, label: valor };
  return (
    <Badge variant="light" color={info.color} size="sm">
      {info.label}
    </Badge>
  );
}

/** Píldora activo/inactivo a partir de un booleano. */
export function ActivoBadge({ activo }: { activo: boolean }) {
  return (
    <Badge variant="light" color={activo ? "success" : "light"} size="sm">
      {activo ? "Activo" : "Inactivo"}
    </Badge>
  );
}
