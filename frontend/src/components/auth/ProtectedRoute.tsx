import { Navigate, Outlet } from "react-router";
import { useAuth } from "../../context/AuthContext";

// Envuelve las rutas del panel: sin token -> redirige a /signin.
export default function ProtectedRoute() {
  const { token, cargando } = useAuth();

  if (cargando) {
    return (
      <div className="flex items-center justify-center min-h-screen text-gray-500">
        Cargando...
      </div>
    );
  }

  return token ? <Outlet /> : <Navigate to="/signin" replace />;
}
