import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { LoginPage } from "./pages/LoginPage";
import { DashboardLayout } from "./components/DashboardLayout";
import { Dashboard } from "./pages/Dashboard";
import { SchedulesPage } from "./pages/SchedulesPage";
import { TeachersPage } from "./pages/TeachersPage";
import { ClassesPage } from "./pages/ClassesPage";
import { SubjectsPage } from "./pages/SubjectsPage";
import { CalendarPage } from "./pages/CalendarPage";
import { StudentLoginPage } from "./pages/student/StudentLoginPage";
import { StudentLayout } from "./components/StudentLayout";
import { StudentDashboard } from "./pages/student/StudentDashboard";
import { StudentSchedulePage } from "./pages/student/StudentSchedulePage";
import { StudentSubjectsPage } from "./pages/student/StudentSubjectsPage";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Root redirect */}
        <Route path="/" element={<Navigate to="/guru" replace />} />

        {/* Teacher/Admin Portal */}
        <Route path="/guru" element={<LoginPage />} />
        <Route path="/guru" element={<DashboardLayout />}>
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="schedules" element={<SchedulesPage />} />
          <Route path="calendar" element={<CalendarPage />} />
          <Route path="teachers" element={<TeachersPage />} />
          <Route path="classes" element={<ClassesPage />} />
          <Route path="subjects" element={<SubjectsPage />} />
        </Route>

        {/* Student Portal */}
        <Route path="/siswa" element={<StudentLoginPage />} />
        <Route path="/siswa" element={<StudentLayout />}>
          <Route path="dashboard" element={<StudentDashboard />} />
          <Route path="schedule" element={<StudentSchedulePage />} />
          <Route path="subjects" element={<StudentSubjectsPage />} />
        </Route>

        {/* Catch all */}
        <Route path="*" element={<Navigate to="/guru" replace />} />
      </Routes>
    </BrowserRouter>
  );
}
