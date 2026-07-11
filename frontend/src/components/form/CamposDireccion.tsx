import Label from "./Label";
import Input from "./input/InputField";

interface DireccionValues {
  ciudad: string;
  zona: string;
  calle: string;
  nro: string;
  referencia: string;
}

interface CamposDireccionProps {
  value: DireccionValues;
  onChange: (field: keyof DireccionValues, val: string) => void;
  errores?: Record<string, string[]>;
}

export default function CamposDireccion({ value, onChange, errores = {} }: CamposDireccionProps) {
  return (
    <div className="space-y-3 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
      <p className="text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</p>
      <div className="grid grid-cols-2 gap-3">
        <div>
          <Label>Ciudad</Label>
          <Input value={value.ciudad} onChange={(e) => onChange("ciudad", e.target.value)} />
          {errores["direccion.ciudad"] && <p className="mt-1 text-xs text-error-500">{errores["direccion.ciudad"][0]}</p>}
        </div>
        <div>
          <Label>Zona</Label>
          <Input value={value.zona} onChange={(e) => onChange("zona", e.target.value)} />
        </div>
      </div>
      <div className="grid grid-cols-2 gap-3">
        <div>
          <Label>Calle</Label>
          <Input value={value.calle} onChange={(e) => onChange("calle", e.target.value)} />
        </div>
        <div>
          <Label>Nro</Label>
          <Input value={value.nro} onChange={(e) => onChange("nro", e.target.value)} />
        </div>
      </div>
      <div>
        <Label>Referencia</Label>
        <Input value={value.referencia} onChange={(e) => onChange("referencia", e.target.value)} />
      </div>
    </div>
  );
}
