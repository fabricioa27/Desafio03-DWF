package sv.edu.udb.Repositorios;

import sv.edu.udb.Modelos.Event;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface EventRepository extends JpaRepository<Event, Integer> {
    // JpaRepository ya nos incluye lo que son los
    // métodos como:
    // * findAll().
    // * findById().
    // * save().
    // * deleteById().
}
