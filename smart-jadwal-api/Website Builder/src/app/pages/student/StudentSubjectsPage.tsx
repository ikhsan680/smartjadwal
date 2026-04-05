import { BookOpen, User, Calendar, Clock } from "lucide-react";

interface Subject {
  id: number;
  name: string;
  teacher: string;
  schedules: {
    day: string;
    time: string;
    room: string;
  }[];
}

export function StudentSubjectsPage() {
  const studentClass = sessionStorage.getItem("studentClass") || "X RPL 1";

  const subjects: Subject[] = [
    {
      id: 1,
      name: "Mathematics",
      teacher: "John Doe",
      schedules: [
        { day: "Monday", time: "07:30 - 09:00", room: "R101" },
        { day: "Wednesday", time: "09:15 - 10:45", room: "R101" },
      ],
    },
    {
      id: 2,
      name: "English",
      teacher: "Jane Smith",
      schedules: [
        { day: "Monday", time: "09:15 - 10:45", room: "R102" },
        { day: "Thursday", time: "07:30 - 09:00", room: "R102" },
      ],
    },
    {
      id: 3,
      name: "Physics",
      teacher: "David Lee",
      schedules: [
        { day: "Monday", time: "11:00 - 12:30", room: "R103" },
      ],
    },
    {
      id: 4,
      name: "Programming",
      teacher: "Bob Wilson",
      schedules: [
        { day: "Monday", time: "12:45 - 14:15", room: "Lab 1" },
        { day: "Wednesday", time: "11:00 - 12:30", room: "Lab 1" },
        { day: "Friday", time: "09:15 - 10:45", room: "Lab 1" },
      ],
    },
    {
      id: 5,
      name: "Database",
      teacher: "Alice Brown",
      schedules: [
        { day: "Tuesday", time: "07:30 - 09:00", room: "Lab 2" },
        { day: "Thursday", time: "09:15 - 10:45", room: "Lab 2" },
      ],
    },
    {
      id: 6,
      name: "Web Design",
      teacher: "Charlie Davis",
      schedules: [
        { day: "Tuesday", time: "09:15 - 10:45", room: "Lab 1" },
        { day: "Thursday", time: "12:45 - 14:15", room: "Lab 1" },
      ],
    },
    {
      id: 7,
      name: "Chemistry",
      teacher: "Emma White",
      schedules: [
        { day: "Tuesday", time: "11:00 - 12:30", room: "Lab 3" },
      ],
    },
    {
      id: 8,
      name: "Physical Education",
      teacher: "Frank Green",
      schedules: [
        { day: "Tuesday", time: "14:30 - 16:00", room: "Field" },
      ],
    },
    {
      id: 9,
      name: "Indonesian",
      teacher: "John Doe",
      schedules: [
        { day: "Wednesday", time: "07:30 - 09:00", room: "R104" },
      ],
    },
    {
      id: 10,
      name: "Computer Network",
      teacher: "Charlie Davis",
      schedules: [
        { day: "Wednesday", time: "12:45 - 14:15", room: "Lab 2" },
      ],
    },
    {
      id: 11,
      name: "History",
      teacher: "David Lee",
      schedules: [
        { day: "Thursday", time: "11:00 - 12:30", room: "R105" },
      ],
    },
    {
      id: 12,
      name: "Project Management",
      teacher: "Frank Green",
      schedules: [
        { day: "Friday", time: "07:30 - 09:00", room: "R201" },
      ],
    },
  ];

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">My Subjects</h1>
        <p className="text-gray-600">All subjects for {studentClass}</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {subjects.map((subject) => (
          <div key={subject.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div className="flex items-start gap-3 mb-4">
              <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <BookOpen className="w-6 h-6 text-blue-600" />
              </div>
              <div className="flex-1">
                <h3 className="text-lg text-gray-900 mb-1">{subject.name}</h3>
                <div className="flex items-center gap-2 text-sm text-gray-600">
                  <User className="w-4 h-4" />
                  <span>{subject.teacher}</span>
                </div>
              </div>
            </div>

            <div className="border-t border-gray-100 pt-4">
              <div className="flex items-center gap-2 mb-3">
                <Calendar className="w-4 h-4 text-gray-500" />
                <span className="text-sm text-gray-700">Class Schedule:</span>
              </div>
              <div className="space-y-2">
                {subject.schedules.map((schedule, index) => (
                  <div key={index} className="bg-blue-50 rounded-lg p-3">
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-sm text-blue-900">{schedule.day}</span>
                      <span className="text-xs text-gray-600">{schedule.room}</span>
                    </div>
                    <div className="flex items-center gap-2 text-xs text-blue-700">
                      <Clock className="w-3 h-3" />
                      <span>{schedule.time}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="mt-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm p-6 text-white">
        <h3 className="text-xl mb-2">Total Subjects: {subjects.length}</h3>
        <p className="text-blue-100">
          Stay focused and organized with your class schedule!
        </p>
      </div>
    </div>
  );
}
