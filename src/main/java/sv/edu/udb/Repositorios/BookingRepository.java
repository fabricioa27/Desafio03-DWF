package sv.edu.udb.Repositorios;

import sv.edu.udb.Modelos.Booking;
import sv.edu.udb.Modelos.User;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface BookingRepository extends JpaRepository<Booking, Integer> {
    List<Booking> findByUser(User user);
}
