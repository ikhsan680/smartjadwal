import { ImageWithFallback } from "./figma/ImageWithFallback";

export function About() {
  return (
    <section id="about" className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <div className="order-2 md:order-1">
            <ImageWithFallback
              src="https://images.unsplash.com/photo-1741466071751-c62474d2d3ae?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxjcmVhdGl2ZSUyMHdvcmtzcGFjZSUyMGxhcHRvcHxlbnwxfHx8fDE3NzI2Nzc3MzZ8MA&ixlib=rb-4.1.0&q=80&w=1080&utm_source=figma&utm_medium=referral"
              alt="Creative workspace"
              className="rounded-lg shadow-xl w-full"
            />
          </div>
          <div className="order-1 md:order-2">
            <h2 className="text-4xl mb-6 text-gray-900">
              About Our Company
            </h2>
            <p className="text-lg text-gray-600 mb-6">
              We're a team of passionate innovators dedicated to creating exceptional digital experiences. With years of expertise and a commitment to excellence, we help businesses transform their vision into reality.
            </p>
            <p className="text-lg text-gray-600 mb-8">
              Our approach combines cutting-edge technology with human-centered design to deliver solutions that not only meet but exceed expectations.
            </p>
            <div className="grid grid-cols-3 gap-8">
              <div>
                <div className="text-3xl text-blue-600 mb-2">500+</div>
                <div className="text-gray-600">Projects</div>
              </div>
              <div>
                <div className="text-3xl text-blue-600 mb-2">50+</div>
                <div className="text-gray-600">Team Members</div>
              </div>
              <div>
                <div className="text-3xl text-blue-600 mb-2">98%</div>
                <div className="text-gray-600">Satisfaction</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
