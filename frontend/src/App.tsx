import { BrowserRouter as Router, Routes, Route } from "react-router";
import SignIn from "./pages/AuthPages/SignIn";
import SignUp from "./pages/AuthPages/SignUp";
import NotFound from "./pages/OtherPage/NotFound";
import UserProfiles from "./pages/UserProfiles";
import Videos from "./pages/UiElements/Videos";
import Images from "./pages/UiElements/Images";
import Alerts from "./pages/UiElements/Alerts";
import Badges from "./pages/UiElements/Badges";
import Avatars from "./pages/UiElements/Avatars";
import Buttons from "./pages/UiElements/Buttons";
import LineChart from "./pages/Charts/LineChart";
import BarChart from "./pages/Charts/BarChart";
import Calendar from "./pages/Calendar";
import BasicTables from "./pages/Tables/BasicTables";
import FormElements from "./pages/Forms/FormElements";
import Blank from "./pages/Blank";
import AppLayout from "./layout/AppLayout";
import { ScrollToTop } from "./components/common/ScrollToTop";
import Home from "./pages/Dashboard/Home";
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
import Direcciones from "./pages/Direcciones/Direcciones";
import Alertas from "./pages/Alertas/Alertas";
import Asignaciones from "./pages/Asignaciones/Asignaciones";

export default function App() {
  return (
    <>
      <Router>
        <ScrollToTop />
        <Routes>
          {/* Dashboard Layout (protegido: requiere sesión) */}
          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index path="/" element={<Home />} />

              {/* Panel / reportes */}
              <Route path="/panel" element={<Panel />} />
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

              {/* Direcciones / Alertas / Asignaciones */}
              <Route path="/direcciones" element={<Direcciones />} />
              <Route path="/alertas" element={<Alertas />} />
              <Route path="/asignaciones" element={<Asignaciones />} />

              {/* Plantilla (demo) */}
              <Route path="/profile" element={<UserProfiles />} />
              <Route path="/calendar" element={<Calendar />} />
              <Route path="/blank" element={<Blank />} />
              <Route path="/form-elements" element={<FormElements />} />
              <Route path="/basic-tables" element={<BasicTables />} />
              <Route path="/alerts" element={<Alerts />} />
              <Route path="/avatars" element={<Avatars />} />
              <Route path="/badge" element={<Badges />} />
              <Route path="/buttons" element={<Buttons />} />
              <Route path="/images" element={<Images />} />
              <Route path="/videos" element={<Videos />} />
              <Route path="/line-chart" element={<LineChart />} />
              <Route path="/bar-chart" element={<BarChart />} />
            </Route>
          </Route>

          {/* Auth Layout */}
          <Route path="/signin" element={<SignIn />} />
          <Route path="/signup" element={<SignUp />} />

          {/* Fallback Route */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </Router>
    </>
  );
}
