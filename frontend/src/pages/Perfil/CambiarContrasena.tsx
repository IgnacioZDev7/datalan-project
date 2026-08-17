import { useState, FormEvent } from "react";
import { AxiosError } from "axios";
import PageMeta from "../../components/common/PageMeta";
import api from "../../services/api";
import { useAuth } from "../../context/AuthContext";
import Button from "../../components/ui/button/Button";
import Input from "../../components/form/input/InputField";
import Label from "../../components/form/Label";

const FORM_VACIO = { contrasena_actual: "", contrasena: "", contrasena_confirmation: "" };

export default function CambiarContrasena() {
  const { usuario } = useAuth();
  const [form, setForm] = useState(FORM_VACIO);
  const [errores, setErrores] = useState<Record<string, string[]>>({});
  const [guardando, setGuardando] = useState(false);
  const [ok, setOk] = useState(false);

  async function guardar(e: FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setErrores({});
    setOk(false);
    try {
      await api.put("/perfil/contrasena", form);
      setForm(FORM_VACIO);
      setOk(true);
    } catch (err) {
      const axErr = err as AxiosError<{ errors?: Record<string, string[]>; message?: string }>;
      setErrores(axErr.response?.data?.errors ?? { general: [axErr.response?.data?.message ?? "No se pudo cambiar la contraseña."] });
    } finally {
      setGuardando(false);
    }
  }

  const err = (campo: string) => errores[campo] && <p className="mt-1 text-xs text-error-500">{errores[campo][0]}</p>;

  return (
    <>
      <PageMeta title="Cambiar contraseña | DATALAN" description="Cambio de contraseña del usuario" />

      <div className="mb-6">
        <h1 className="text-xl font-semibold text-gray-800 dark:text-white/90">Cambiar contraseña</h1>
        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Sesión de <span className="font-medium">{usuario?.correo_electronico}</span>
        </p>
      </div>

      <div className="max-w-lg p-6 bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
        {ok && (
          <div className="p-3 mb-4 text-sm rounded-lg text-success-700 bg-success-50 dark:bg-success-500/10 dark:text-success-400">
            ✓ Contraseña actualizada correctamente.
          </div>
        )}
        {errores.general && (
          <div className="p-3 mb-4 text-sm rounded-lg text-error-600 bg-error-50 dark:bg-error-500/10">{errores.general[0]}</div>
        )}

        <form onSubmit={guardar} className="space-y-4">
          <div>
            <Label>Contraseña actual *</Label>
            <Input type="password" value={form.contrasena_actual} onChange={(e) => setForm({ ...form, contrasena_actual: e.target.value })} />
            {err("contrasena_actual")}
          </div>
          <div>
            <Label>Nueva contraseña *</Label>
            <Input type="password" value={form.contrasena} onChange={(e) => setForm({ ...form, contrasena: e.target.value })} />
            <p className="mt-1 text-xs text-gray-400">Mínimo 8 caracteres, con letras y números.</p>
            {err("contrasena")}
          </div>
          <div>
            <Label>Confirmar nueva contraseña *</Label>
            <Input type="password" value={form.contrasena_confirmation} onChange={(e) => setForm({ ...form, contrasena_confirmation: e.target.value })} />
            {err("contrasena_confirmation")}
          </div>
          <div className="flex justify-end pt-2">
            <Button size="sm" disabled={guardando}>{guardando ? "Guardando..." : "Cambiar contraseña"}</Button>
          </div>
        </form>
      </div>
    </>
  );
}
