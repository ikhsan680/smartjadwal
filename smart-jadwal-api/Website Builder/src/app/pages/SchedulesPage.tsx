import { useState } from "react";
import { Plus, Edit, Trash2, Search } from "lucide-react";

interface Schedule {
  id: number;
  class: string;
  subject: string;
  teacher: string;
  day: string;
  startTime: string;
  endTime: string;
  room: string;
}

export function SchedulesPage() {
  const [schedules] = useState<Schedule[]>([
    { id: 1, class: "X RPL 1", subject: "Mathematics", teacher: "John Doe", day: "Monday", startTime: "07:30", endTime: "09:00", room: "R101" },
    { id: 2, class: "X RPL 1", subject: "English", teacher: "Jane Smith", day: "Monday", startTime: "09:15", endTime: "10:45", room: "R102" },
    { id: 3, class: "XI RPL 2", subject: "Programming", teacher: "Bob Wilson", day: "Tuesday", startTime: "07:30", endTime: "09:00", room: "Lab 1" },
    { id: 4, class: "XI RPL 2", subject: "Database", teacher: "Alice Brown", day: "Tuesday", startTime: "09:15", endTime: "10:45", room: "Lab 2" },
    { id: 5, class: "XII RPL 1", subject: "Web Design", teacher: "Charlie Davis", day: "Wednesday", startTime: "07:30", endTime: "09:00", room: "Lab 1" },
    { id: 6, class: "X RPL 2", subject: "Physics", teacher: "David Lee", day: "Wednesday", startTime: "09:15", endTime: "10:45", room: "R103" },
    { id: 7, class: "XI RPL 1", subject: "Chemistry", teacher: "Emma White", day: "Thursday", startTime: "07:30", endTime: "09:00", room: "Lab 3" },
    { id: 8, class: "XII RPL 2", subject: "Project Management", teacher: "Frank Green", day: "Thursday", startTime: "09:15", endTime: "10:45", room: "R201" },
  ]);

  const [filterClass, setFilterClass] = useState("");
  const [filterTeacher, setFilterTeacher] = useState("");

  const filteredSchedules = schedules.filter((schedule) => {
    const matchClass = filterClass === "" || schedule.class.includes(filterClass);
    const matchTeacher = filterTeacher === "" || schedule.teacher.toLowerCase().includes(filterTeacher.toLowerCase());
    return matchClass && matchTeacher;
  });

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Schedule Management</h1>
        <p className="text-gray-600">Manage class schedules and timetables</p>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="p-6 border-b border-gray-200">
          <div className="flex flex-col lg:flex-row gap-4 justify-between">
            <div className="flex flex-col sm:flex-row gap-4 flex-1">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Filter by class..."
                  value={filterClass}
                  onChange={(e) => setFilterClass(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
                />
              </div>
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Filter by teacher..."
                  value={filterTeacher}
                  onChange={(e) => setFilterTeacher(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
                />
              </div>
            </div>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 whitespace-nowrap">
              <Plus className="w-5 h-5" />
              Add Schedule
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Class</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Subject</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Teacher</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Day</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Start Time</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">End Time</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Room</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredSchedules.map((schedule) => (
                <tr key={schedule.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.class}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.subject}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.teacher}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.day}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.startTime}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.endTime}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{schedule.room}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    <div className="flex gap-2">
                      <button className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                        <Edit className="w-4 h-4" />
                      </button>
                      <button className="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {filteredSchedules.length === 0 && (
          <div className="p-8 text-center text-gray-500">
            No schedules found matching your filters.
          </div>
        )}
      </div>
    </div>
  );
}
