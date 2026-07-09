import {
  createContext,
  useContext,
  useEffect,
  useState,
  ReactNode,
} from "react";
import api from "../services/api";

export interface Usuario {
  id: number;
  nombres: string;
  nombre_completo: string;
  correo_electronico: string;
  cargo: string | null;
  roles: string[];
  permisos: string[];
}

interface AuthContextType {
  usuario: Usuario | null;
  token: string | null;
  cargando: boolean;
  login: (correo: string, contrasena: string) => Promise<void>;
  logout: () => Promise<void>;
  puede: (permiso: string) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [usuario, setUsuario] = useState<Usuario | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem("token"));
  const [cargando, setCargando] = useState<boolean>(true);

  // Al cargar, si hay token, recupera el usuario autenticado.
  useEffect(() => {
    if (token) {
      api
        .get("/me")
        .then((r) => setUsuario(r.data.data))
        .catch(() => {
          localStorage.removeItem("token");
          setToken(null);
        })
        .finally(() => setCargando(false));
    } else {
      setCargando(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const login = async (correo: string, contrasena: string) => {
    const r = await api.post("/login", {
      correo_electronico: correo,
      contrasena,
    });
    localStorage.setItem("token", r.data.token);
    setToken(r.data.token);
    setUsuario(r.data.usuario);
  };

  const logout = async () => {
    try {
      await api.post("/logout");
    } catch {
      // aunque falle en el servidor, cerramos localmente
    }
    localStorage.removeItem("token");
    setToken(null);
    setUsuario(null);
  };

  const puede = (permiso: string) => usuario?.permisos?.includes(permiso) ?? false;

  return (
    <AuthContext.Provider value={{ usuario, token, cargando, login, logout, puede }}>
      {children}
    </AuthContext.Provider>
  );
}

// eslint-disable-next-line react-refresh/only-export-components
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth debe usarse dentro de <AuthProvider>");
  return ctx;
}
