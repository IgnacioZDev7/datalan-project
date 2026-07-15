import { BrowserRouter as Router, Routes, Route } from "react-router";
import SignIn from "./pages/AuthPages/SignIn";
import NotFound from "./pages/OtherPage/NotFound";
import AppLayout from "./layout/AppLayout";
import { ScrollToTop } from "./components/common/ScrollToTop";
import ProtectedRoute from "./components/auth/ProtectedRoute";

// --- Pantallas del sistema DATALAN ---
import Panel from "./pages/Dashboard/Panel";
import Productos from "./pages/Catalogo/Productos";
import Categorias from "./pages/Catalogo/Categorias";
import Marcas from "./pages/Catalogo/Marcas";
import Modelos from "./pages/Catalogo/Modelos";
import UnidadesMedida from "./pages/Catalogo/UnidadesMedida";
import Empresas from "./pages/Terceros/Empresas";
import Proveedores from "./pages/Terceros/Proveedores";
import Tecnicos from "./pages/Terceros/Tecnicos";
import Proyectos from "./pages/Proyectos/Proyectos";
import Almacenes from "./pages/Inventario/Almacenes";
import Ubicaciones from "./pages/Inventario/Ubicaciones";
import Activos from "./pages/Inventario/Activos";
import Carretes from "./pages/Inventario/Carretes";
import Existencias from "./pages/Inventario/Existencias";
import Movimientos from "./pages/Inventario/Movimientos";
import Kardex from "./pages/Inventario/Kardex";
import Alertas from "./pages/Alertas/Alertas";
import Asignaciones from "./pages/Asignaciones/Asignaciones";
import Usuarios from "./pages/Usuarios/Usuarios";
import InventariosFisicos from "./pages/Inventario/InventariosFisicos";
import Bitacora from "./pages/Administracion/Bitacora";

export default function App() {
  return (
    <>
      <Router>
        <ScrollToTop />
        <Routes>
          {/* Panel (protegido: requiere sesión) */}
          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index path="/" element={<Panel />} />

              {/* Reportes */}
              <Route path="/movimientos" element={<Movimientos />} />
              <Route path="/kardex" element={<Kardex />} />

              {/* Catálogo */}
              <Route path="/productos" element={<Productos />} />
              <Route path="/categorias" element={<Categorias />} />
              <Route path="/marcas" element={<Marcas />} />
              <Route path="/modelos" element={<Modelos />} />
              <Route path="/unidades-medida" element={<UnidadesMedida />} />

              {/* Terceros */}
              <Route path="/empresas" element={<Empresas />} />
              <Route path="/proveedores" element={<Proveedores />} />
              <Route path="/tecnicos" element={<Tecnicos />} />

              {/* Proyectos */}
              <Route path="/proyectos" element={<Proyectos />} />

              {/* Inventario */}
              <Route path="/almacenes" element={<Almacenes />} />
              <Route path="/ubicaciones" element={<Ubicaciones />} />
              <Route path="/activos" element={<Activos />} />
              <Route path="/carretes" element={<Carretes />} />
              <Route path="/existencias" element={<Existencias />} />

              {/* Alertas / Asignaciones */}
              <Route path="/alertas" element={<Alertas />} />
              <Route path="/asignaciones" element={<Asignaciones />} />

              {/* Administración */}
              <Route path="/usuarios" element={<Usuarios />} />
              <Route path="/bitacora" element={<Bitacora />} />

              {/* Inventario físico */}
              <Route path="/inventarios-fisicos" element={<InventariosFisicos />} />
            </Route>
          </Route>

          {/* Autenticación */}
          <Route path="/signin" element={<SignIn />} />

          {/* Fallback */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </Router>
    </>
  );
}
